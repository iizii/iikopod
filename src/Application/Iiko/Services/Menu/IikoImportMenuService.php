<?php

declare(strict_types=1);

namespace Application\Iiko\Services\Menu;

use Application\Iiko\Builders\ItemBuilder;
use Application\Iiko\Builders\ItemGroupBuilder;
use Application\Iiko\Builders\ItemModifierGroupBuilder;
use Application\Iiko\Builders\ItemSizeBuilder;
use Application\Iiko\Builders\MenuBuilder;
use Application\Iiko\Builders\NutritionBuilder;
use Application\Iiko\Builders\PriceBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Repositories\IikoMenuItemGroupRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemModifierGroupRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemPriceRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemNutritionRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemPriceRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PriceCategory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Shared\Domain\ValueObjects\StringId;

final readonly class IikoImportMenuService
{
    public function __construct(
        private IikoAuthenticator $authenticator,
        private IikoConnectorInterface $iikoConnector,
        private DatabaseManager $databaseManager,
        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
        private IikoMenuRepositoryInterface $menuRepository,
        private IikoMenuItemGroupRepositoryInterface $iikoMenuItemGroupRepository,
        private IikoMenuItemRepositoryInterface $iikoMenuItemRepository,
        private IikoMenuItemSizeRepositoryInterface $iikoMenuItemSizeRepository,
        private IikoMenuItemModifierGroupRepositoryInterface $iikoMenuItemModifierGroupRepository,
        private IikoMenuItemModifierItemRepositoryInterface $iikoMenuItemModifierItemRepository,
        private IikoMenuItemPriceRepositoryInterface $iikoMenuItemPriceRepository,
        private IikoMenuItemNutritionRepositoryInterface $iikoMenuItemNutritionRepository,
        private IikoMenuItemModifierItemPriceRepositoryInterface $iikoMenuItemModifierItemPriceRepository,
    ) {}

    /**
     * Основной метод импорта меню из Iiko
     *
     * @throws ConnectionException
     * @throws RequestException
     * @throws \Throwable
     */
    public function handle(): void
    {
        // Получаем список всех организаций с настройками
        $organizations = $this->organizationSettingRepository->all();

        $organizations->each(function (OrganizationSetting $organizationSetting): void {
            // Для каждой ценовой категории организации
            $organizationSetting->priceCategories->each(
                function (PriceCategory $priceCategory) use ($organizationSetting): void {
                    Cache::set('current_import_price_category_id', $priceCategory->categoryId->id, 1000);
                    try {
                        sleep(10);
                        // Запрашиваем меню из Iiko API
                        $responseEntity = $this
                            ->iikoConnector
                            ->getMenu(
                                new GetMenuRequestData(
                                    [$organizationSetting->iikoRestaurantId->id],
                                    $organizationSetting->externalMenuId->id,
                                    $priceCategory->categoryId->id,
                                ),
                                $this->authenticator->getAuthToken($organizationSetting->iikoApiKey),
                            )
                            ->toDomainEntity(); // Преобразуем DTO в доменную сущность
                    } catch (\Throwable $e) {
                        Cache::delete('current_import_price_category_id');
                        // Обработка ошибок запроса
                        throw new \RuntimeException(
                            sprintf(
                                'Не удалось получить меню из Iiko, для ресторана: %s, меню: %s, ценовая категория %s ошибка: %s',
                                $organizationSetting->iikoRestaurantId->id,
                                $organizationSetting->externalMenuId->id,
                                $priceCategory->categoryId->id,
                                $e->getMessage(),
                            ),
                        );
                    }

                    // Сохраняем данные меню и связанных сущностей в транзакции
                    /**
                     * Обновление и создание меню не обсервится(на момент написания кода) и не вызывает
                     * при created|updated никаких последующих методов.
                     */
                    $this
                        ->databaseManager
                        ->transaction(function () use ($priceCategory, $organizationSetting, $responseEntity): void {
                            $menuBuilder = MenuBuilder::fromExisted($responseEntity);
                            $menuBuilder = $menuBuilder->setOrganizationSettingId($organizationSetting->id);

                            // Создание или обновление самого меню
                            $createdMenu = $this->menuRepository->createOrUpdate($menuBuilder->build());

                            // Повторная установка ID созданного меню в билдер
                            $menuBuilder = $menuBuilder->setId($createdMenu->id);

                            // Обработка групп блюд
                            $this->handleItemGroups($menuBuilder->build(), $priceCategory);
                        });
                },
            );
            Cache::delete('current_import_price_category_id');
        });
    }

    /**
     * Обработка групп блюд меню
     */
    private function handleItemGroups(Menu $menu, PriceCategory $priceCategory): void
    {
        /**
         * IikoMenuItemGroup обсервится и при update|create вызываются ивенты продолжающие выполнять связанный с этим местом код
         */
        $menu
            ->itemGroups
            ->each(function (ItemGroup $itemGroup) use ($priceCategory, $menu) {
                $itemGroupBuilder = ItemGroupBuilder::fromExisted($itemGroup);
                $itemGroupBuilder = $itemGroupBuilder
                    ->setIikoMenuId($menu->id)
                    ->setExternalId(new StringId(sprintf('%s:%s', $priceCategory->prefix, $itemGroup->externalId->id))) // Тестово отказались ->setExternalId(new StringId(sprintf('%s:%s', $priceCategory->prefix, $itemGroup->externalId->id)))
                    ->setName(sprintf('%s %s', $priceCategory->prefix, $itemGroup->name));

                // Создание/обновление группы
                $createdGroup = $this->iikoMenuItemGroupRepository->createOrUpdate($itemGroupBuilder->build());

                $itemGroupBuilder = $itemGroupBuilder->setId($createdGroup->id);

                // Обработка блюд внутри группы
                $this->handleItemGroupItems($itemGroupBuilder->build(), $priceCategory);
            });
    }

    /**
     * Обработка блюд в группе
     */
    private function handleItemGroupItems(ItemGroup $itemGroup, PriceCategory $priceCategory): void
    {
        /**
         * IikoMenuItem обсервится и при update|create вызываются ивенты продолжающие выполнять связанный с этим местом код
         */
        $itemGroup
            ->items
            ->each(function (Item $item) use ($itemGroup, $priceCategory) {
                $itemBuilder = ItemBuilder::fromExisted($item);
                $itemBuilder = $itemBuilder
                    ->setItemGroupId($itemGroup->id)
                    ->setPrefix($priceCategory->prefix);

                // Создание/обновление блюда
                $createdItem = $this->iikoMenuItemRepository->createOrUpdate($itemBuilder->build());

                $itemBuilder = $itemBuilder->setId($createdItem->id);

                // Обработка размеров блюда
                $this->handleItemGroupItemSizes($itemBuilder->build(), $priceCategory);
            });
    }

    /**
     * Обработка размеров блюда
     */
    private function handleItemGroupItemSizes(Item $item, PriceCategory $priceCategory): void
    {
        $item
            ->itemSizes
            ->each(function (ItemSize $itemSize) use ($item, $priceCategory) {
                // Создаем билдер на основе текущего размера
                $itemSizeBuilder = ItemSizeBuilder::fromExisted($itemSize)
                    ->setItemId($item->id);
                // Создаем или обновляем размер блюда
                $createdItemSize = $this->iikoMenuItemSizeRepository->createOrUpdate($itemSizeBuilder->build());
                logger('itemSize', [
                    'gettedItemSize' => $itemSize->toArray(),
                    '$createdItemSize' => $createdItemSize->toArray(),
                    'itemSize' => $itemSizeBuilder->build()->toArray()
                ]);

                // Обновляем билдер с новым ID из БД
                $itemSizeBuilder = $itemSizeBuilder
                    ->setId($createdItemSize->id)
                    ->build();

                // Обработка цен, нутриентов и модификаторов
                $this->handleItemSizePrices($itemSizeBuilder, $priceCategory);
                $this->handleItemSizeNutritions($itemSizeBuilder, $priceCategory);
                $this->handleItemModifierGroups($itemSizeBuilder, $priceCategory);
            });
    }

    /**
     * Обработка цен конкретного размера блюда
     */
    private function handleItemSizePrices(ItemSize $itemSize, PriceCategory $priceCategory): void
    {
        $itemSize
            ->prices
            ->each(function (Price $price) use ($itemSize, $priceCategory) {
                $priceBuilder = PriceBuilder::fromExisted($price);
                $priceBuilder = $priceBuilder
                    ->setItemId($itemSize->id)
                    ->setPriceCategoryId($priceCategory->categoryId);

                $this->iikoMenuItemPriceRepository->createOrUpdate($priceBuilder->build());
            });
    }

    /**
     * Обработка нутриентов (белки, жиры, углеводы и т.п.)
     */
    private function handleItemSizeNutritions(ItemSize $itemSize, PriceCategory $priceCategory): void
    {
        $itemSize
            ->nutritions
            ->each(function (Nutrition $nutrition) use ($itemSize, $priceCategory) {
                $nutritionBuilder = NutritionBuilder::fromExisted($nutrition);
                $nutritionBuilder = $nutritionBuilder
                    ->setItemSizeId($itemSize->id)
                    ->setPriceCategoryId($priceCategory->categoryId);

                $this->iikoMenuItemNutritionRepository->createOrUpdate($nutritionBuilder->build());
            });
    }

    /**
     * Обработка групп модификаторов (например: добавить сыр, убрать соус)
     */
    private function handleItemModifierGroups(ItemSize $itemSize, PriceCategory $priceCategory): void
    {
        $itemSize
            ->itemModifierGroups
            ->each(function (ItemModifierGroup $itemModifierGroup) use ($itemSize, $priceCategory) {
                $itemModifierBuilder = ItemModifierGroupBuilder::fromExisted($itemModifierGroup);
                $itemModifierBuilder = $itemModifierBuilder->setItemSizeId($itemSize->id);

                $createdModifierGroup = $this->iikoMenuItemModifierGroupRepository->createOrUpdate(
                    $itemModifierBuilder->build(),
                );

                $itemModifierBuilder = $itemModifierBuilder->setId($createdModifierGroup->id);

                // Обработка модификаторов внутри группы
                $this->handleModifierItems($itemModifierBuilder->build(), $priceCategory);
            });
    }

    /**
     * Обработка самих модификаторов (например: сыр, бекон, соус)
     */
    private function handleModifierItems(ItemModifierGroup $modifierGroup, PriceCategory $priceCategory): void
    {
        $modifierGroup
            ->items
            ->each(function (Item $item) use ($modifierGroup, $priceCategory) {
                $itemBuilder = ItemBuilder::fromExisted($item);
                $itemBuilder = $itemBuilder->setItemGroupId($modifierGroup->id);

                $createdItem = $this->iikoMenuItemModifierItemRepository->createOrUpdate($itemBuilder->build());

                $itemBuilder = $itemBuilder->setId($createdItem->id);

                // Обработка цен модификатора
                $this->handleModifierItemPrices($itemBuilder->build(), $priceCategory);
            });
    }

    /**
     * Обработка цен для модификатора
     */
    private function handleModifierItemPrices(Item $item, PriceCategory $priceCategory): void
    {
        $item
            ->prices
            ->each(function (Price $price) use ($item, $priceCategory) {
                $priceBuilder = PriceBuilder::fromExisted($price);
                $priceBuilder = $priceBuilder
                    ->setItemId($item->id)
                    ->setPriceCategoryId($priceCategory->categoryId);

                $this->iikoMenuItemModifierItemPriceRepository->createOrUpdate($priceBuilder->build());
            });
    }
}

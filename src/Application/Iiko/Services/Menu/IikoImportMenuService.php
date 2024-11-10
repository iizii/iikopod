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
     * @throws ConnectionException
     * @throws RequestException
     * @throws \Throwable
     */
    public function handle(): void
    {
        $organizations = $this->organizationSettingRepository->all();

        $organizations->each(function (OrganizationSetting $organizationSetting): void {
            $organizationSetting->priceCategories->each(
                function (PriceCategory $priceCategory) use ($organizationSetting): void {
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
                        ->toDomainEntity();

                    $this
                        ->databaseManager
                        ->transaction(function () use ($priceCategory, $organizationSetting, $responseEntity): void {
                            $menuBuilder = MenuBuilder::fromExisted($responseEntity);
                            $menuBuilder = $menuBuilder->setOrganizationSettingId($organizationSetting->id);

                            $createdMenu = $this->menuRepository->createOrUpdate($menuBuilder->build());

                            $menuBuilder = $menuBuilder->setId($createdMenu->id);

                            $this->handleItemGroups($menuBuilder->build(), $priceCategory);
                        });
                },
            );
        });
    }

    private function handleItemGroups(Menu $menu, PriceCategory $priceCategory): void
    {
        $menu
            ->itemGroups
            ->each(function (ItemGroup $itemGroup) use ($priceCategory, $menu) {
                $itemGroupBuilder = ItemGroupBuilder::fromExisted($itemGroup);
                $itemGroupBuilder = $itemGroupBuilder
                    ->setIikoMenuId($menu->id)
                    ->setExternalId(new StringId(sprintf('%s:%s', $priceCategory->prefix, $itemGroup->externalId->id)))
                    ->setName(sprintf('%s %s', $priceCategory->prefix, $itemGroup->name));

                $createdGroup = $this->iikoMenuItemGroupRepository->createOrUpdate($itemGroupBuilder->build());

                $itemGroupBuilder = $itemGroupBuilder->setId($createdGroup->id);

                $this->handleItemGroupItems($itemGroupBuilder->build());
            });
    }

    private function handleItemGroupItems(ItemGroup $itemGroup): void
    {
        $itemGroup
            ->items
            ->each(function (Item $item) use ($itemGroup) {
                $itemBuilder = ItemBuilder::fromExisted($item);
                $itemBuilder = $itemBuilder->setItemGroupId($itemGroup->id);

                $createdItem = $this->iikoMenuItemRepository->createOrUpdate($itemBuilder->build());

                $itemBuilder = $itemBuilder->setId($createdItem->id);

                $this->handleItemGroupItemSizes($itemBuilder->build());
            });
    }

    private function handleItemGroupItemSizes(Item $item): void
    {
        $item
            ->itemSizes
            ->each(function (ItemSize $itemSize) use ($item) {
                $itemSizeBuilder = ItemSizeBuilder::fromExisted($itemSize);
                $itemSizeBuilder = $itemSizeBuilder->setItemId($item->id);

                $createdItemSize = $this->iikoMenuItemSizeRepository->createOrUpdate($itemSizeBuilder->build());

                $itemSize = $itemSizeBuilder
                    ->setId($createdItemSize->id)
                    ->build();

                $this->handleItemSizePrices($itemSize);
                $this->handleItemSizeNutritions($itemSize);
                $this->handleItemModifierGroups($itemSize);
            });
    }

    private function handleItemSizePrices(ItemSize $itemSize): void
    {
        $itemSize
            ->prices
            ->each(function (Price $price) use ($itemSize) {
                $priceBuilder = PriceBuilder::fromExisted($price);
                $priceBuilder = $priceBuilder->setItemId($itemSize->id);

                $this->iikoMenuItemPriceRepository->createOrUpdate($priceBuilder->build());
            });
    }

    private function handleItemSizeNutritions(ItemSize $itemSize): void
    {
        $itemSize
            ->nutritions
            ->each(function (Nutrition $nutrition) use ($itemSize) {
                $nutritionBuilder = NutritionBuilder::fromExisted($nutrition);
                $nutritionBuilder = $nutritionBuilder->setItemSizeId($itemSize->id);

                $this->iikoMenuItemNutritionRepository->createOrUpdate($nutritionBuilder->build());
            });
    }

    private function handleItemModifierGroups(ItemSize $itemSize): void
    {
        $itemSize
            ->itemModifierGroups
            ->each(function (ItemModifierGroup $itemModifierGroup) use ($itemSize) {
                $itemModifierBuilder = ItemModifierGroupBuilder::fromExisted($itemModifierGroup);
                $itemModifierBuilder = $itemModifierBuilder->setItemSizeId($itemSize->id);

                $createdModifierGroup = $this->iikoMenuItemModifierGroupRepository->createOrUpdate($itemModifierBuilder->build());

                $itemModifierBuilder = $itemModifierBuilder->setId($createdModifierGroup->id);

                $this->handleModifierItems($itemModifierBuilder->build());
            });
    }

    private function handleModifierItems(ItemModifierGroup $modifierGroup): void
    {
        $modifierGroup
            ->items
            ->each(function (Item $item) use ($modifierGroup) {
                $itemBuilder = ItemBuilder::fromExisted($item);
                $itemBuilder = $itemBuilder->setItemGroupId($modifierGroup->id);

                $createdItem = $this->iikoMenuItemModifierItemRepository->createOrUpdate($itemBuilder->build());

                $itemBuilder = $itemBuilder->setId($createdItem->id);

                $this->handleModifierItemPrices($itemBuilder->build());
            });
    }

    private function handleModifierItemPrices(Item $item): void
    {
        $item
            ->prices
            ->each(function (Price $price) use ($item) {
                $priceBuilder = PriceBuilder::fromExisted($price);
                $priceBuilder = $priceBuilder->setItemId($item->id);

                $this->iikoMenuItemModifierItemPriceRepository->createOrUpdate($priceBuilder->build());
            });
    }
}

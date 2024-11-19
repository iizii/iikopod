<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Application\WelcomeGroup\Builders\FoodBuilder;
use Application\WelcomeGroup\Builders\ModifierTypeBuilder;
use Application\WelcomeGroup\Services\ModifierHandlerService;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Enums\ModifierTypeBehaviour;
use Domain\WelcomeGroup\Exceptions\FoodUpdateException;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ItemContext;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierRequestData;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType;
use Shared\Domain\ValueObjects\IntegerId;

final class UpdateFoodJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Item $item) {}

    /**
     * Execute the job.
     */
    public function handle(
        IikoMenuRepositoryInterface $iikoMenuRepository,
        IikoMenuItemSizeRepositoryInterface $iikoMenuItemSizeRepository,
        OrganizationSettingRepositoryInterface $organizationSettingRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodCategoryRepositoryInterface $welcomeGroupFoodCategoryRepository,
        WelcomeGroupFoodRepositoryInterface $welcomeGroupFoodRepository,
        WelcomeGroupModifierTypeRepositoryInterface $welcomeGroupModifierTypeRepository,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
        ModifierHandlerService $modifierService
    ): void {
        // Получаем блюдо айки из ивента
        $itemContext = $this->fetchItemContext($iikoMenuRepository, $organizationSettingRepository, $welcomeGroupFoodCategoryRepository, $welcomeGroupFoodRepository);

        if (! $itemContext) {
            return;
        }

        $foodRequestData = new EditFoodRequestData(
            $itemContext->category->id->id,
            $itemContext->organizationSetting->welcomeGroupDefaultWorkshopId->id,
            $itemContext->food->name,
            $itemContext->food->description,
            $itemContext->food->weight,
            $itemContext->food->caloricity,
            $itemContext->food->price,
        );

        try {
            $foodResponse = $welcomeGroupConnector->updateFood($foodRequestData, $itemContext->food->id);
        } catch (RequestException $e) {
            logger()
                ->channel('food_update')
                ->error(
                    'Не удалось произвести обновление товара. Причина: '.$e->getMessage(),
                    [
                        'food' => $itemContext->food,
                        'requestData' => $foodRequestData->toArray(),
                        'exception' => $e,
                    ]
                );
            throw new FoodUpdateException('Не удалось произвести обновление товара. Причина: '.$e->getMessage(), $e->getCode());
        }

        //        $modifierService->handleModifierGroups($itemContext->food, $itemContext->itemBuilder->modifiers);

        // Получили размеры блюда с ценами, кбжу, модификаторами
        $iikoMenuItemSizes = $iikoMenuItemSizeRepository->findForWithAllRelations($itemContext->item);

        $foodBuilder = FoodBuilder::fromExisted($foodResponse->toDomainEntity())
            ->setWorkshopId($itemContext->organizationSetting->welcomeGroupDefaultWorkshopId)
            ->setInternalFoodCategoryId($itemContext->category->id)
            ->setExternalFoodCategoryId($itemContext->category->externalId);

        $food = $foodBuilder->build();

        $iikoMenuItemSizes->each(function (ItemSize $itemSize) use (
            $welcomeGroupFoodModifierRepository,
            $food,
            $welcomeGroupModifierRepository,
            $welcomeGroupModifierTypeRepository,
            $welcomeGroupConnector,
            $itemContext
        ): void {
            $this->handleModifierGroups(
                $food,
                $itemSize->itemModifierGroups,
                $welcomeGroupConnector,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupModifierRepository,
                $welcomeGroupFoodModifierRepository,
                $itemContext
            );
        });
    }

    private function handleModifierGroups(
        Food $food,
        ItemModifierGroupCollection $modifierGroupCollection,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupModifierTypeRepositoryInterface $welcomeGroupModifierTypeRepository,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
        ItemContext $itemContext
    ): void {
        $modifierGroupCollection->each(
            function (ItemModifierGroup $itemModifierGroup) use (
                $welcomeGroupFoodModifierRepository,
                $food,
                $welcomeGroupModifierRepository,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupConnector,
                $itemContext
            ): void {
                $this->handleModifierGroup(
                    $food,
                    $itemModifierGroup,
                    $welcomeGroupConnector,
                    $welcomeGroupModifierTypeRepository,
                    $welcomeGroupModifierRepository,
                    $welcomeGroupFoodModifierRepository,
                    $itemContext
                );
            },
        );
    }

    private function handleModifierGroup(
        Food $food,
        ItemModifierGroup $modifierGroup,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupModifierTypeRepositoryInterface $welcomeGroupModifierTypeRepository,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
        ItemContext $itemContext
    ): void {
        //        WelcomeGroupModifierType::
        $maxQuantity = $modifierGroup->maxQuantity;
        $modifierTypeCollection = WelcomeGroupModifierType::query()
            ->where('iiko_menu_item_modifier_group_id', $modifierGroup->id)
            ->where('name', $modifierGroup->name)
            ->get();
        // Проверяем максимальное количество модификатора
        if ($maxQuantity === 1) {
            // Проверяем, вдруг у нас в проекте modifierType больше, чем 1 (например сущность обновилась в iiko)
            if ($modifierTypeCollection->count() > 1) {
                $modifierTypeCollection->each(
                    static function (WelcomeGroupModifierType $modifierType, int $i) use (
                        $welcomeGroupConnector,
                        $modifierGroup,
                        $maxQuantity,
                        $welcomeGroupModifierTypeRepository,
                        $itemContext
                    ) {
                        // После запуска перебора, проверяем, первый ли элемент, ибо его мы не удаляем, а обновляем, если maxQuantity = 1
                        if ($i === 1) {
                            $response = $welcomeGroupConnector
                                ->updateModifierType(
                                    new EditModifierTypeRequestData(
                                        $modifierGroup->name,
                                        ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                                    ),
                                    new IntegerId($modifierType->external_id)
                                );

                            $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($response->toDomainEntity());
                            $modifierTypeBuilder->setId(new IntegerId($modifierType->id));
                            $modifierTypeBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                            $welcomeGroupModifierTypeRepository->save($modifierTypeBuilder->build());
                        } else {
                            /*
                             * Удаляем остальные типы модификаторов. При maxQuantity=1 может быть только 1 тип модификатора
                             * Собственно т.к. не первая итерация перебора, то все данные кроме первой итерации должны быть устранены
                             */
                            /** @var Collection $modifiers */
                            $modifiers = $modifierType->modifiers;
                            $modifiers->each(static function (WelcomeGroupModifier $modifier) use ($welcomeGroupConnector, $itemContext): void {
                                $welcomeGroupConnector->updateRestaurantModifier(
                                    new EditRestaurantModifierRequestData(
                                        $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                        $modifier->id,
                                        'blocked'
                                    ),
                                    new IntegerId($modifier->id),
                                );
                            });

                            // Удаление данных с проекта
                            $modifierType->delete();
                        }
                    });
            }
        } else {
            // Начали обрабатывать кейс, когда maxQuantity>1
            // Проверяем сколько modifierType'ов не хватает в системе или наоборот сколько лишних, если число отрицительное
            $count = $modifierTypeCollection->count() - $maxQuantity;

            if ($count > 0) {
                // Сначала перебираем старые и обновляем в соответствии с обновлениями
                $modifierTypeCollection->each(static function (WelcomeGroupModifierType $modifierType) use ($welcomeGroupConnector, $modifierGroup, $maxQuantity, $welcomeGroupModifierTypeRepository) {
                    $response = $welcomeGroupConnector
                        ->updateModifierType(
                            new EditModifierTypeRequestData(
                                $modifierGroup->name,
                                ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                            ),
                            new IntegerId($modifierType->external_id)
                        );

                    $modifierBuilder = ModifierTypeBuilder::fromExisted($response->toDomainEntity());
                    $modifierBuilder->setId(new IntegerId($modifierType->id));
                    $modifierBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                    $welcomeGroupModifierTypeRepository->save($modifierBuilder->build());
                });

                // т.к. по итогу вычислений выявлено, что модификаторов нехватает, то создаём недостающие по количеству = $count

                for ($i = 0; $i < $maxQuantity; $i++) {
                    $modifierTypeResponse = $welcomeGroupConnector->createModifierType(
                        new CreateModifierTypeRequestData(
                            $modifierGroup->name,
                            ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                        ),
                    );

                    $modifierType = $welcomeGroupModifierTypeRepository->save($modifierTypeResponse->toDomainEntity());
                }
            } elseif ($count === 0) {
                // Данный кейс выявил, что новых типов модификаторов создавать не требуется, а вот обновить существуещие необходимо
                $modifierTypeCollection->each(static function (WelcomeGroupModifierType $modifierType) use ($welcomeGroupConnector, $modifierGroup, $maxQuantity, $welcomeGroupModifierTypeRepository) {
                    $response = $welcomeGroupConnector
                        ->updateModifierType(
                            new EditModifierTypeRequestData(
                                $modifierGroup->name,
                                ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                            ),
                            new IntegerId($modifierType->external_id)
                        );

                    $modifierBuilder = ModifierTypeBuilder::fromExisted($response->toDomainEntity());
                    $modifierBuilder->setId(new IntegerId($modifierType->id));
                    $modifierBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                    $welcomeGroupModifierTypeRepository->save($modifierBuilder->build());
                });
            } elseif ($count < 0) {
                // Кейс выявил, что есть лишние типы модификаторов, необходимо удалить то количество, которое указано в $count
                // Получаем коллекцию из последних $excessCount элементов для удаления
                $modifiersToDelete = $modifierTypeCollection->slice($count);

                // Удаляем лишние модификаторы
                $modifiersToDelete->each(static function (WelcomeGroupModifierType $modifierType) {
                    // Удаляем тип модификатора через коннектор
                    // Запрос на удаление данных в WG (надо добавить)

                    // Удаляем тип модификатора из системы
                    $modifierType->delete();
                });

                // Оставшиеся модификаторы
                $remainingModifiers = $modifierTypeCollection->slice(0, $modifierTypeCollection->count() - abs($count));

                // Обновляем оставшиеся модификаторы
                $remainingModifiers->each(static function (WelcomeGroupModifierType $modifierType) use ($welcomeGroupConnector, $modifierGroup, $maxQuantity, $welcomeGroupModifierTypeRepository) {
                    $response = $welcomeGroupConnector
                        ->updateModifierType(
                            new EditModifierTypeRequestData(
                                $modifierGroup->name,
                                ModifierTypeBehaviour::fromValue($maxQuantity)->value
                            ),
                            new IntegerId($modifierType->external_id)
                        );

                    $modifierBuilder = ModifierTypeBuilder::fromExisted($response->toDomainEntity());
                    $modifierBuilder->setId(new IntegerId($modifierType->id));
                    $modifierBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                    $welcomeGroupModifierTypeRepository->save($modifierBuilder->build());
                });
            }
        }
    }

    private function fetchItemContext(
        IikoMenuRepositoryInterface $menuRepository,
        OrganizationSettingRepositoryInterface $orgRepository,
        WelcomeGroupFoodCategoryRepositoryInterface $categoryRepository,
        WelcomeGroupFoodRepositoryInterface $foodRepository
    ): ?ItemContext {
        $item = $this->item;
        $itemBuilder = ItemBuilder::fromExisted($item);

        $menu = $menuRepository->findforItem($item);
        $organizationSetting = $orgRepository->findById($menu?->organizationSettingId);
        $category = $categoryRepository->findByIikoMenuItemGroupId($item->itemGroupId);

        if (! $menu || ! $organizationSetting || ! $category) {
            return null;
        }

        $food = $foodRepository->findByIikoItemId($item->id); // Или соответствующий метод получения еды

        return new ItemContext($item, $itemBuilder, $food, $organizationSetting, $category, $foodRepository);
    }
}

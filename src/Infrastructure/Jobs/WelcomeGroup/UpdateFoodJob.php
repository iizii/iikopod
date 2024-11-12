<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Application\WelcomeGroup\Builders\FoodBuilder;
use Application\WelcomeGroup\Builders\ModifierTypeBuilder;
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
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeRequestData;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
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
    ): void {
        // Получаем блюдо айки из ивента
        $iikoItem = $this->item;

        // Получили размеры блюда с ценами, кбжу, модификаторами
        $iikoMenuItemSizes = $iikoMenuItemSizeRepository->findForWithAllRelations($iikoItem);

        $iikoItemBuilder = ItemBuilder::fromExisted($iikoItem);
        // Построили блюдо со всеми данными
        $iikoItemBuilder = $iikoItemBuilder
            ->setItemSizes($iikoMenuItemSizes)
            ->build();

        // Получили категорию блюда (корндог, напитки и пр.)
        $foodCategory = $welcomeGroupFoodCategoryRepository->findByIikoMenuItemGroupId($iikoItemBuilder->itemGroupId);

        if (! $foodCategory) {
            return;
        }

        // Определили к какому меню относится блюдо
        $iikoMenu = $iikoMenuRepository->findforItem($iikoItemBuilder);

        if (! $iikoMenu) {
            return;
        }

        // Получили настройки ресторана к которому принадлежит блюдо
        $organizationSetting = $organizationSettingRepository->findById($iikoMenu->organizationSettingId);

        if (! $organizationSetting) {
            return;
        }

        $gettingFood = WelcomeGroupFood::toDomainEntity(WelcomeGroupFood::whereIikoMenuItemId($iikoItemBuilder->id->id));
        // Строим блюдо формата требуемого ПОДом в виде DTO
        $foodBuilder = FoodBuilder::fromExisted($gettingFood);
        //        $foodBuilder = FoodBuilder::fromIikoItem($iikoItemBuilder)
        //            ->setWorkshopId($organizationSetting->welcomeGroupDefaultWorkshopId)
        //            ->setInternalFoodCategoryId($foodCategory->id)
        //            ->setExternalFoodCategoryId($foodCategory->externalId);

        // Построили данные, чтобы они стали годными для реквеста ПОДа
        $foodRequest = $foodBuilder->build();

        // Обновили блюдо в ПОДе
        $response = $welcomeGroupConnector->updateFood(
            new EditFoodRequestData(
                $foodRequest->externalFoodCategoryId->id,
                $foodRequest->workshopId->id,
                $foodRequest->name,
                $foodRequest->description,
                $foodRequest->weight,
                $foodRequest->caloricity,
                $foodRequest->price,
            ),
            $foodRequest->externalId
        );

        $foodBuilder = $foodBuilder->setExternalId(new IntegerId($response->id));

        $updatedFood = $welcomeGroupFoodRepository->save($foodBuilder->build());

        $food = $foodBuilder
            ->setId($updatedFood->id)
            ->build();

        $iikoMenuItemSizes->each(function (ItemSize $itemSize) use (
            $welcomeGroupFoodModifierRepository,
            $food,
            $welcomeGroupModifierRepository,
            $welcomeGroupModifierTypeRepository,
            $welcomeGroupConnector
        ): void {
            $this->handleModifierGroups(
                $food,
                $itemSize->itemModifierGroups,
                $welcomeGroupConnector,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupModifierRepository,
                $welcomeGroupFoodModifierRepository,
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
    ): void {
        $modifierGroupCollection->each(
            function (ItemModifierGroup $itemModifierGroup) use (
                $welcomeGroupFoodModifierRepository,
                $food,
                $welcomeGroupModifierRepository,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupConnector
            ): void {
                $this->handleModifierGroup(
                    $food,
                    $itemModifierGroup,
                    $welcomeGroupConnector,
                    $welcomeGroupModifierTypeRepository,
                    $welcomeGroupModifierRepository,
                    $welcomeGroupFoodModifierRepository,
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
                    static function (WelcomeGroupModifierType $modifierType, int $i) use ($welcomeGroupConnector, $modifierGroup, $maxQuantity, $welcomeGroupModifierTypeRepository) {
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

                            $modifierBuilder = ModifierTypeBuilder::fromExisted($response->toDomainEntity());
                            $modifierBuilder->setId(new IntegerId($modifierType->id));
                            $modifierBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                            $welcomeGroupModifierTypeRepository->save($modifierBuilder->build());
                        } else {
                            /*
                             * Удаляем остальные типы модификаторов. При maxQuantity=1 может быть только 1 тип модификатора
                             * Собственно т.к. не первая итерация перебора, то все данные кроме первой итерации должны быть устранены
                             */

                            // Запрос на удаление данных в WG (надо добавить)

                            // Удаление данных с проекта
                            //                            $modifierType->delete();
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
}

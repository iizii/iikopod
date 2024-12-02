<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Application\WelcomeGroup\Builders\FoodBuilder;
use Application\WelcomeGroup\Builders\FoodModifierBuilder;
use Application\WelcomeGroup\Builders\ModifierBuilder;
use Application\WelcomeGroup\Builders\ModifierTypeBuilder;
use Application\WelcomeGroup\Builders\RestaurantFoodBuilder;
use Application\WelcomeGroup\Builders\RestaurantModifierBuilder;
use Application\WelcomeGroup\Services\ModifierHandlerService;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Exceptions\PriceNotLoadedException;
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
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\EditFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ItemContext;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\EditModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierRequestData;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories\WelcomeGroupRestaurantFoodRepository;
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
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository,
        WelcomeGroupRestaurantFoodRepository $welcomeGroupRestaurantFoodRepository,
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
            ->setExternalFoodCategoryId($itemContext->category->externalId)
            ->setId($itemContext->food->id);

        $updatedFood = $welcomeGroupFoodRepository->update($foodBuilder->build());

        $food = $foodBuilder->build();

        $restaurantFoodResponse = $welcomeGroupConnector->updateRestaurantFood(
            new EditRestaurantFoodRequestData(
                $itemContext->organizationSetting->welcomeGroupDefaultWorkshopId->id,
                $food->externalId->id,
            ),
            $itemContext->restaurantFood->id,
        );

        $restaurantFoodBuilder = RestaurantFoodBuilder::fromExisted(
            $restaurantFoodResponse->toDomainEntity()
        )
            ->setWelcomeGroupFoodId($food->id)
            ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->id)
            ->setExternalId(new IntegerId($restaurantFoodResponse->id));

        $updatedRestaurantFood = $welcomeGroupRestaurantFoodRepository->

        $iikoMenuItemSizes->each(function (ItemSize $itemSize) use (
            $welcomeGroupRestaurantModifierRepository,
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
                $itemContext,
                $welcomeGroupRestaurantModifierRepository
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
        ItemContext $itemContext,
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository
    ): void {
        $modifierGroupCollection->each(
            function (ItemModifierGroup $itemModifierGroup) use (
                $welcomeGroupFoodModifierRepository,
                $food,
                $welcomeGroupModifierRepository,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupConnector,
                $itemContext,
                $welcomeGroupRestaurantModifierRepository
            ): void {
                $this->handleModifierGroup(
                    $food,
                    $itemModifierGroup,
                    $welcomeGroupConnector,
                    $welcomeGroupModifierTypeRepository,
                    $welcomeGroupModifierRepository,
                    $welcomeGroupFoodModifierRepository,
                    $itemContext,
                    $welcomeGroupRestaurantModifierRepository
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
        ItemContext $itemContext,
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository
    ): void {
        $maxQuantity = $modifierGroup->maxQuantity;

        $existedModifierTypeCollection = WelcomeGroupModifierType::query()
            ->where('iiko_menu_item_modifier_group_id', $modifierGroup->id)
            ->get();

        for ($i = 0; $i < $maxQuantity; $i++) {
            // Проверяем максимальное количество модификатора
            if ($maxQuantity === 1) {
                // Проверяем, вдруг у нас в проекте modifierType больше, чем 1 (например сущность обновилась в iiko), а должно быть 1, ведь maxQuantity=1
                if ($existedModifierTypeCollection->count() > 1) {
                    $existedModifierTypeCollection->each(
                        static function (WelcomeGroupModifierType $modifierType, int $i) use (
                            $welcomeGroupFoodModifierRepository,
                            $welcomeGroupConnector,
                            $modifierGroup,
                            $maxQuantity,
                            $welcomeGroupModifierTypeRepository,
                            $itemContext,
                            $welcomeGroupModifierRepository,
                            $welcomeGroupRestaurantModifierRepository

                        ) {
                            // После запуска перебора, проверяем, первый ли элемент, ибо его мы не удаляем, а обновляем, если maxQuantity = 1
                            if ($i === 1) {
                                $updatedModifierTypeResponse = $welcomeGroupConnector
                                    ->updateModifierType(
                                        new EditModifierTypeRequestData(
                                            $modifierGroup->name,
                                            ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                                        ),
                                        new IntegerId($modifierType->external_id)
                                    );

                                $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($updatedModifierTypeResponse->toDomainEntity())
                                    ->setId(new IntegerId($modifierType->id))
                                    ->setIikoMenuItemModifierGroupId(new IntegerId($modifierGroup->id->id));

                                // Закончили обновление типа модификатора
                                $welcomeGroupModifierTypeRepository->save($modifierTypeBuilder->build());

                                // Начали перебирать сами модификаторы чтобы прикрепить их к modifierType
                                $modifierGroup->items->each(
                                    static function (Item $item) use (
                                        $welcomeGroupFoodModifierRepository,
                                        $welcomeGroupRestaurantModifierRepository,
                                        $updatedModifierTypeResponse,
                                        $modifierTypeBuilder,
                                        $welcomeGroupModifierRepository,
                                        $welcomeGroupConnector,
                                        $itemContext
                                    ) {
                                        $modifierType = $modifierTypeBuilder->build();

                                        // Ищем существует ли модификатор
                                        $modifier = $welcomeGroupModifierRepository
                                            ->findByInternalModifierTypeIdAndIikoExternalId(
                                                $modifierType->id,
                                                $item->externalId
                                            );
                                        // Если модификатор найден, то обновляем существующий модификатор в WG
                                        if ($modifier) {
                                            $updateModifierResponse = $welcomeGroupConnector->updateModifier(
                                                new EditModifierRequestData(
                                                    $item->name,
                                                    (int) $modifierType
                                                        ->externalId
                                                        ->id,
                                                    false
                                                ),
                                                $modifier->externalId
                                            );

                                            $modifierBuilder = ModifierBuilder::fromExisted($updateModifierResponse->toDomainEntity())
                                                ->setExternalId(new IntegerId($updateModifierResponse->id))
                                                ->setIikoExternalModifierId($item->externalId)
                                                ->setInternalModifierTypeId($modifierType->id)
                                                ->setId($modifier->id);

                                            $modifier = $modifierBuilder->build();
                                            // Обновляем модификатор в нашей базе
                                            $updatedModifier = $welcomeGroupModifierRepository->update($modifier);
                                        } else {
                                            // Обработка кейса, если модификатор не найден
                                            // Создаём модификатор в базе WG
                                            $modifierResponse = $welcomeGroupConnector->createModifier(
                                                new CreateModifierRequestData(
                                                    $item->name,
                                                    $updatedModifierTypeResponse->id,
                                                ),
                                            );

                                            $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity())
                                                ->setExternalId(new IntegerId($modifierResponse->id))
                                                ->setInternalModifierTypeId($modifierType->id)
                                                ->setIikoExternalModifierId($item->externalId);
                                            // Сохраняем модификатор в нашу базу
                                            $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
                                            $modifierBuilder = $modifierBuilder->setId($createdModifier->id);
                                            $modifier = $modifierBuilder->build();
                                        }
                                        // Ищем связь ресторана с модификатором в нашей базе.
                                        $restaurantModifier = $welcomeGroupRestaurantModifierRepository->findByInternalRestaurantAndModifierId($itemContext->organizationSetting->id, $modifier->id);
                                        // При отсутствии связи создаём её в WG. Кстати обновления связи не делал, пока показалось, что не требуется
                                        if (! $restaurantModifier) {
                                            $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
                                                new CreateRestaurantModifierRequestData(
                                                    $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                                    $modifier->externalId->id
                                                )
                                            );

                                            $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                            $restaurantModifier = $restaurantModifierBuilder
                                                ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                                ->setWelcomeGroupModifierId($modifier->id);
                                            // Создаём связь модификатора с рестораном в нашей базе
                                            $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());

                                            $restaurantModifier = $createdRestaurantModifier;
                                        }

                                        $itemPrice = $item->prices->first();

                                        if (! $itemPrice) {
                                            throw new PriceNotLoadedException(sprintf('Price not loaded for item %s', $item->id->id));
                                        }
                                        // Ищем связь блюда с модификатором в нашей базе
                                        $foodModifier = $welcomeGroupFoodModifierRepository->findByInternalFoodAndModifierIds($itemContext->food->id, $modifier->id);
                                        // Если связь блюда с модификатором отсутствует в нашей базе, то создаём её в WG
                                        if (! $foodModifier) {
                                            $createFoodModifierResponse = $welcomeGroupConnector->createFoodModifier(
                                                new CreateFoodModifierRequestData(
                                                    $itemContext->food->externalId->id,
                                                    $modifier->externalId->id,
                                                    $item->weight,
                                                    $itemPrice->price ?? 0,
                                                ),
                                            );

                                            $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                                $createFoodModifierResponse->toDomainEntity(),
                                            );
                                            $foodModifierBuilder = $foodModifierBuilder
                                                ->setInternalModifierId($modifier->id)
                                                ->setInternalFoodId($itemContext->food->id);
                                            // Созданяем связь блюда с модификатором в нашу БД
                                            $welcomeGroupFoodModifierRepository->save($foodModifierBuilder->build());
                                        }
                                    }
                                );
                            } else {
                                /*
                                 * Удаляем остальные типы модификаторов. При maxQuantity=1 может быть только 1 тип модификатора
                                 * Собственно т.к. не первая итерация перебора, то все данные кроме первой итерации должны быть устранены
                                 */
                                $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($modifierType->toDomainEntity($modifierType));

                                $modifierGroup->items->each(
                                    static function (Item $item) use (

                                        $welcomeGroupRestaurantModifierRepository,
                                        $modifierTypeBuilder,
                                        $welcomeGroupModifierRepository,
                                        $welcomeGroupConnector,
                                        $itemContext
                                    ) {
                                        $modifierType = $modifierTypeBuilder->build();

                                        // Ищем существует ли модификатор
                                        $modifier = $welcomeGroupModifierRepository
                                            ->findByInternalModifierTypeIdAndIikoExternalId(
                                                $modifierType->id,
                                                $item->externalId
                                            );

                                        // Если модификатор найден, то обновляем существующий модификатор в WG
                                        if (! $modifier) {
                                            return;
                                        }
                                        // Ищем связь ресторана с модификатором в нашей базе.
                                        $restaurantModifier = $welcomeGroupRestaurantModifierRepository->findByInternalRestaurantAndModifierId($itemContext->organizationSetting->id, $modifier->id);
                                        // При отсутствии связи создаём её в WG. Кстати обновления связи не делал, пока показалось, что не требуется
                                        $updatedRestaurantModifier = $welcomeGroupConnector->updateRestaurantModifier(
                                            new EditRestaurantModifierRequestData(
                                                $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                                $modifier->externalId->id,
                                                'blocked'
                                            ),
                                            new IntegerId($restaurantModifier->externalId->id),
                                        );

                                        $welcomeGroupRestaurantModifierRepository->deleteByInternalId($restaurantModifier->id);
                                    }
                                );

                                // Удаление данных с проекта
                                $modifierType->delete();
                            }
                        });
                } else {
                    $existedModifierTypeCollection->each(
                        static function (WelcomeGroupModifierType $modifierType, int $i) use (
                            $welcomeGroupFoodModifierRepository,
                            $welcomeGroupConnector,
                            $modifierGroup,
                            $maxQuantity,
                            $welcomeGroupModifierTypeRepository,
                            $itemContext,
                            $welcomeGroupModifierRepository,
                            $welcomeGroupRestaurantModifierRepository

                        ) {
                            // После запуска перебора, проверяем, первый ли элемент, ибо его мы не удаляем, а обновляем, если maxQuantity = 1
                            $updatedModifierTypeResponse = $welcomeGroupConnector
                                ->updateModifierType(
                                    new EditModifierTypeRequestData(
                                        $modifierGroup->name,
                                        ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                                    ),
                                    new IntegerId($modifierType->external_id)
                                );

                            $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($updatedModifierTypeResponse->toDomainEntity())
                                ->setId(new IntegerId($modifierType->id))
                                ->setIikoMenuItemModifierGroupId(new IntegerId($modifierGroup->id->id));

                            // Закончили обновление типа модификатора
                            $welcomeGroupModifierTypeRepository->save($modifierTypeBuilder->build());

                            // Начали перебирать сами модификаторы чтобы прикрепить их к modifierType
                            $modifierGroup->items->each(
                                static function (Item $item) use (
                                    $welcomeGroupFoodModifierRepository,
                                    $welcomeGroupRestaurantModifierRepository,
                                    $updatedModifierTypeResponse,
                                    $modifierTypeBuilder,
                                    $welcomeGroupModifierRepository,
                                    $welcomeGroupConnector,
                                    $itemContext
                                ) {
                                    $modifierType = $modifierTypeBuilder->build();

                                    // Ищем существует ли модификатор
                                    $modifier = $welcomeGroupModifierRepository
                                        ->findByInternalModifierTypeIdAndIikoExternalId(
                                            $modifierType->id,
                                            $item->externalId
                                        );
                                    // Если модификатор найден, то обновляем существующий модификатор в WG
                                    if ($modifier) {
                                        $updateModifierResponse = $welcomeGroupConnector->updateModifier(
                                            new EditModifierRequestData(
                                                $item->name,
                                                (int) $modifierType
                                                    ->externalId
                                                    ->id,
                                                false
                                            ),
                                            $modifier->externalId
                                        );

                                        $modifierBuilder = ModifierBuilder::fromExisted($updateModifierResponse->toDomainEntity())
                                            ->setExternalId(new IntegerId($updateModifierResponse->id))
                                            ->setIikoExternalModifierId($item->externalId)
                                            ->setInternalModifierTypeId($modifierType->id)
                                            ->setId($modifier->id);

                                        $modifier = $modifierBuilder->build();
                                        // Обновляем модификатор в нашей базе
                                        $updatedModifier = $welcomeGroupModifierRepository->update($modifier);
                                    } else {
                                        // Обработка кейса, если модификатор не найден
                                        // Создаём модификатор в базе WG
                                        $modifierResponse = $welcomeGroupConnector->createModifier(
                                            new CreateModifierRequestData(
                                                $item->name,
                                                $updatedModifierTypeResponse->id,
                                            ),
                                        );

                                        $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity())
                                            ->setExternalId(new IntegerId($modifierResponse->id))
                                            ->setInternalModifierTypeId($modifierType->id)
                                            ->setIikoExternalModifierId($item->externalId);
                                        // Сохраняем модификатор в нашу базу
                                        $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
                                        $modifierBuilder = $modifierBuilder->setId($createdModifier->id);
                                        $modifier = $modifierBuilder->build();
                                    }
                                    // Ищем связь ресторана с модификатором в нашей базе.
                                    $restaurantModifier = $welcomeGroupRestaurantModifierRepository->findByInternalRestaurantAndModifierId($itemContext->organizationSetting->id, $modifier->id);
                                    // При отсутствии связи создаём её в WG. Кстати обновления связи не делал, пока показалось, что не требуется
                                    if (! $restaurantModifier) {
                                        $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
                                            new CreateRestaurantModifierRequestData(
                                                $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                                $modifier->externalId->id
                                            )
                                        );

                                        $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                        $restaurantModifier = $restaurantModifierBuilder
                                            ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                            ->setWelcomeGroupModifierId($modifier->id);
                                        // Создаём связь модификатора с рестораном в нашей базе
                                        $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());

                                        $restaurantModifier = $createdRestaurantModifier;
                                    }

                                    $itemPrice = $item->prices->first();

                                    if (! $itemPrice) {
                                        throw new PriceNotLoadedException(sprintf('Price not loaded for item %s', $item->id->id));
                                    }
                                    // Ищем связь блюда с модификатором в нашей базе
                                    $foodModifier = $welcomeGroupFoodModifierRepository->findByInternalFoodAndModifierIds($itemContext->food->id, $modifier->id);
                                    // Если связь блюда с модификатором отсутствует в нашей базе, то создаём её в WG
                                    if (! $foodModifier) {
                                        $createFoodModifierResponse = $welcomeGroupConnector->createFoodModifier(
                                            new CreateFoodModifierRequestData(
                                                $itemContext->food->externalId->id,
                                                $modifier->externalId->id,
                                                $item->weight,
                                                $itemPrice->price ?? 0,
                                            ),
                                        );

                                        $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                            $createFoodModifierResponse->toDomainEntity(),
                                        );
                                        $foodModifierBuilder = $foodModifierBuilder
                                            ->setInternalModifierId($modifier->id)
                                            ->setInternalFoodId($itemContext->food->id);
                                        // Созданяем связь блюда с модификатором в нашу БД
                                        $welcomeGroupFoodModifierRepository->save($foodModifierBuilder->build());
                                    }
                                }
                            );
                        });
                }
            } else {
                // Начали обрабатывать кейс, когда maxQuantity>1
                // Проверяем сколько modifierType'ов не хватает в системе или наоборот сколько лишних, если число отрицительное
                $missingCount = $maxQuantity - $existedModifierTypeCollection->count();

                // Отсутствует больше 0 необходимых модификаторов. Нужно создать столько сколько не хватает
                if ($missingCount > 0) {
                    // Сначала перебираем старые и обновляем в соответствии с обновлениями
                    $existedModifierTypeCollection->each(static function (WelcomeGroupModifierType $modifierType) use (
                        $itemContext,
                        $welcomeGroupModifierRepository,
                        //                        $updatedModifierTypeResponse,
                        $welcomeGroupRestaurantModifierRepository,
                        $welcomeGroupFoodModifierRepository,
                        $welcomeGroupConnector,
                        $modifierGroup,
                        $maxQuantity,
                        $welcomeGroupModifierTypeRepository
                    ) {
                        $updatedModifierTypeResponse = $welcomeGroupConnector
                            ->updateModifierType(
                                new EditModifierTypeRequestData(
                                    $modifierGroup->name,
                                    ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                                ),
                                new IntegerId($modifierType->external_id)
                            );

                        $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($updatedModifierTypeResponse->toDomainEntity());
                        $modifierTypeBuilder->setId(new IntegerId($modifierType->id));
                        $modifierTypeBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                        // Обновили тип модификатора
                        $welcomeGroupModifierTypeRepository->update($modifierTypeBuilder->build());

                        // Перебираем айтемы группы модификаторов из IIKO
                        $modifierGroup->items->each(
                            static function (Item $item) use (
                                $welcomeGroupFoodModifierRepository,
                                $welcomeGroupRestaurantModifierRepository,
                                $updatedModifierTypeResponse,
                                $modifierTypeBuilder,
                                $welcomeGroupModifierRepository,
                                $welcomeGroupConnector,
                                $itemContext
                            ) {
                                $modifierType = $modifierTypeBuilder->build();

                                // Получаем модификатор из нашей БД
                                $modifier = $welcomeGroupModifierRepository
                                    ->findByInternalModifierTypeIdAndIikoExternalId(
                                        $modifierType->id,
                                        $item->externalId
                                    );
                                // Если модификтор найден, то просто обновляем его в WG
                                if ($modifier) {
                                    $updateModifierResponse = $welcomeGroupConnector->updateModifier(
                                        new EditModifierRequestData(
                                            $item->name,
                                            (int) $modifierType
                                                ->externalId
                                                ->id,
                                            false
                                        ),
                                        $modifier->externalId
                                    );

                                    $modifierBuilder = ModifierBuilder::fromExisted($updateModifierResponse->toDomainEntity())
//                                        ->setExternalId(new IntegerId($updateModifierResponse->id))
                                        ->setIikoExternalModifierId($item->externalId)
                                        ->setInternalModifierTypeId($modifierType->id)
                                        ->setId($modifier->id);

                                    $modifier = $modifierBuilder->build();
                                    // Сохраняем обновлённый модификатор во внутреннюю таблицу WG
                                    $updatedModifier = $welcomeGroupModifierRepository->update($modifier);
                                } else {
                                    // Если модификатор не был найден, то создаём его в вг и внутренней базе
                                    $modifierResponse = $welcomeGroupConnector->createModifier(
                                        new CreateModifierRequestData(
                                            $item->name,
                                            $updatedModifierTypeResponse->id,
                                        ),
                                    );

                                    $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity())
//                                        ->setExternalId(new IntegerId($modifierResponse->id))
                                        ->setInternalModifierTypeId($modifierType->id)
                                        ->setIikoExternalModifierId($item->externalId);

                                    $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
                                    $modifier = $modifierBuilder->setId($createdModifier->id);
                                }
                                // Ищем связь модификатора с рестораном во внутренней БД
                                $restaurantModifier = $welcomeGroupRestaurantModifierRepository->findByInternalRestaurantAndModifierId($itemContext->organizationSetting->id, $modifier->id);

                                // При отсутствии связи модификатора с рестораном -- создаём, при наличии обновляем
                                if (! $restaurantModifier) {
                                    $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
                                        new CreateRestaurantModifierRequestData(
                                            $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                            $modifier->externalId->id
                                        )
                                    );

                                    $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                    $restaurantModifier = $restaurantModifierBuilder
                                        ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                        ->setWelcomeGroupModifierId($modifier->id);

                                    $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());
                                    $restaurantModifier = $restaurantModifier->setId($createdRestaurantModifier->id);
                                } else {
                                    $restaurantModifierResponse = $welcomeGroupConnector->updateRestaurantModifier(
                                        new EditRestaurantModifierRequestData(
                                            $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                            $modifier->externalId->id
                                        ),
                                        $restaurantModifier->id
                                    );

                                    $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                    $restaurantModifier = $restaurantModifierBuilder
                                        ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                        ->setWelcomeGroupModifierId($modifier->id);

                                    $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->update($restaurantModifier->build());
                                    $restaurantModifier = $restaurantModifier->setId($createdRestaurantModifier->id);

                                }

                                // Получаем стоимость товара. В теории нам кажется, что прайса может быть больше чем 1, но
                                // ни айка (CHICKO), ни WG не умеют работать с разными размерами товаров и их прайсами
                                $itemPrice = $item->prices->first();

                                if (! $itemPrice) {
                                    throw new PriceNotLoadedException(sprintf('Price not loaded for item %s', $item->id->id));
                                }

                                // Ищем во внутренней БД связь модификатора с блюдом
                                $foodModifier = $welcomeGroupFoodModifierRepository->findByInternalFoodAndModifierIds($itemContext->food->id, $modifier->id);

                                // Если не нашли связь модификатора с блюдом, то создаём её в WG и во внутренней базе
                                if (! $foodModifier) {
                                    $createFoodModifierResponse = $welcomeGroupConnector->createFoodModifier(
                                        new CreateFoodModifierRequestData(
                                            $itemContext->food->externalId->id,
                                            $modifier->externalId->id,
                                            $item->weight,
                                            $itemPrice->price ?? 0,
                                        ),
                                    );

                                    $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                        $createFoodModifierResponse->toDomainEntity(),
                                    );
                                    $foodModifierBuilder = $foodModifierBuilder
                                        ->setInternalModifierId($modifier->id)
                                        ->setInternalFoodId($itemContext->food->id);

                                    // Процесс создания связи во внутренней базе
                                    $welcomeGroupFoodModifierRepository->save($foodModifierBuilder->build());
                                } else {
                                    $createFoodModifierResponse = $welcomeGroupConnector->updateFoodModifier(
                                        new EditFoodModifierRequestData(
                                            $itemContext->food->externalId->id,
                                            $modifier->externalId->id,
                                            $item->weight,
                                            0,
                                            $itemPrice->price ?? 0,
                                            0
                                        ),
                                        $foodModifier->id
                                    );

                                    $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                        $createFoodModifierResponse->toDomainEntity(),
                                    );
                                    $foodModifierBuilder = $foodModifierBuilder
                                        ->setInternalModifierId($modifier->id)
                                        ->setInternalFoodId($itemContext->food->id)
                                        ->setId($foodModifier->id);

                                    // Процесс создания связи во внутренней базе
                                    $welcomeGroupFoodModifierRepository->update($foodModifierBuilder->build());
                                }
                            }
                        );
                    });

                    // т.к. по итогу вычислений выявлено, что модификаторов не хватает, то создаём недостающие по количеству = $count
                    for ($i = 0; $i < $missingCount; $i++) {
                        $modifierTypeResponse = $welcomeGroupConnector->createModifierType(
                            new CreateModifierTypeRequestData(
                                $modifierGroup->name,
                                ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                            ),
                        );

                        $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($modifierTypeResponse->toDomainEntity())
                            ->setIikoMenuItemModifierGroupId($modifierGroup->id);

                        $modifierType = $welcomeGroupModifierTypeRepository->save($modifierTypeResponse->toDomainEntity());

                        $modifierGroup->items->each(
                            static function (Item $item) use (
                                $itemContext,
                                $welcomeGroupFoodModifierRepository,
                                $food,
                                $modifierType,
                                $welcomeGroupModifierRepository,
                                $modifierTypeResponse,
                                $welcomeGroupConnector,
                                $welcomeGroupRestaurantModifierRepository
                            ) {
                                $modifierResponse = $welcomeGroupConnector->createModifier(
                                    new CreateModifierRequestData(
                                        $item->name,
                                        $modifierTypeResponse->id,
                                    ),
                                );

                                $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity());
                                $modifier = $modifierBuilder
                                    ->setExternalId(new IntegerId($modifierResponse->id))
                                    ->setInternalModifierTypeId($modifierType->id)
                                    ->setIikoExternalModifierId($item->externalId)
                                    ->build();

                                $createdModifier = $welcomeGroupModifierRepository->save($modifier);

                                $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
                                    new CreateRestaurantModifierRequestData(
                                        $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                        $createdModifier->externalId->id
                                    )
                                );

                                $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                $restaurantModifier = $restaurantModifierBuilder
                                    ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                    ->setWelcomeGroupModifierId($createdModifier->id);

                                $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());

                                $restaurantModifier->setExternalId($createdRestaurantModifier->id);

                                $itemPrice = $item->prices->first();

                                if (! $itemPrice) {
                                    throw new PriceNotLoadedException(sprintf('Price not loaded for item %s', $item->id->id));
                                }

                                $createFoodModifierResponse = $welcomeGroupConnector->createFoodModifier(
                                    new CreateFoodModifierRequestData(
                                        $food->externalId->id,
                                        $modifier->externalId->id,
                                        $item->weight,
                                        $itemPrice->price ?? 0,
                                    ),
                                );

                                $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                    $createFoodModifierResponse->toDomainEntity(),
                                );
                                $foodModifierBuilder = $foodModifierBuilder
                                    ->setInternalModifierId($createdModifier->id)
                                    ->setInternalFoodId($food->id);

                                $welcomeGroupFoodModifierRepository->save($foodModifierBuilder->build());
                            },
                        );
                    }
                } elseif ($missingCount === 0) {
                    // Данный кейс выявил, что новых типов модификаторов создавать не требуется, а вот обновить существуещие необходимо
                    $existedModifierTypeCollection->each(static function (WelcomeGroupModifierType $modifierType) use (
                        $itemContext,
                        $welcomeGroupModifierRepository,
                        //                        $updatedModifierTypeResponse,
                        $welcomeGroupRestaurantModifierRepository,
                        $welcomeGroupFoodModifierRepository,
                        $welcomeGroupConnector,
                        $modifierGroup,
                        $maxQuantity,
                        $welcomeGroupModifierTypeRepository
                    ) {
                        $updatedModifierTypeResponse = $welcomeGroupConnector
                            ->updateModifierType(
                                new EditModifierTypeRequestData(
                                    $modifierGroup->name,
                                    ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                                ),
                                new IntegerId($modifierType->external_id)
                            );

                        $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($updatedModifierTypeResponse->toDomainEntity());
                        $modifierTypeBuilder->setId(new IntegerId($modifierType->id));
                        $modifierTypeBuilder->setIikoMenuItemModifierGroupId(new IntegerId($modifierType->id));

                        // Обновили тип модификатора
                        $welcomeGroupModifierTypeRepository->update($modifierTypeBuilder->build());

                        // Перебираем айтемы группы модификаторов из IIKO
                        $modifierGroup->items->each(
                            static function (Item $item) use (
                                $welcomeGroupFoodModifierRepository,
                                $welcomeGroupRestaurantModifierRepository,
                                $updatedModifierTypeResponse,
                                $modifierTypeBuilder,
                                $welcomeGroupModifierRepository,
                                $welcomeGroupConnector,
                                $itemContext
                            ) {
                                $modifierType = $modifierTypeBuilder->build();

                                // Получаем модификатор из нашей БД
                                $modifier = $welcomeGroupModifierRepository
                                    ->findByInternalModifierTypeIdAndIikoExternalId(
                                        $modifierType->id,
                                        $item->externalId
                                    );
                                // Если модификтор найден, то просто обновляем его в WG
                                if ($modifier) {
                                    $updateModifierResponse = $welcomeGroupConnector->updateModifier(
                                        new EditModifierRequestData(
                                            $item->name,
                                            (int) $modifierType
                                                ->externalId
                                                ->id,
                                            false
                                        ),
                                        $modifier->externalId
                                    );

                                    $modifierBuilder = ModifierBuilder::fromExisted($updateModifierResponse->toDomainEntity())
//                                        ->setExternalId(new IntegerId($updateModifierResponse->id))
                                        ->setIikoExternalModifierId($item->externalId)
                                        ->setInternalModifierTypeId($modifierType->id)
                                        ->setId($modifier->id);

                                    $modifier = $modifierBuilder->build();
                                    // Сохраняем обновлённый модификатор во внутреннюю таблицу WG
                                    $updatedModifier = $welcomeGroupModifierRepository->update($modifier);
                                } else {
                                    // Если модификатор не был найден, то создаём его в вг и внутренней базе
                                    $modifierResponse = $welcomeGroupConnector->createModifier(
                                        new CreateModifierRequestData(
                                            $item->name,
                                            $updatedModifierTypeResponse->id,
                                        ),
                                    );

                                    $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity())
//                                        ->setExternalId(new IntegerId($modifierResponse->id))
                                        ->setInternalModifierTypeId($modifierType->id)
                                        ->setIikoExternalModifierId($item->externalId);

                                    $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
                                    $modifier = $modifierBuilder->setId($createdModifier->id);
                                }
                                // Ищем связь модификатора с рестораном во внутренней БД
                                $restaurantModifier = $welcomeGroupRestaurantModifierRepository->findByInternalRestaurantAndModifierId($itemContext->organizationSetting->id, $modifier->id);

                                // При отсутствии связи модификатора с рестораном -- создаём, при наличии обновляем
                                if (! $restaurantModifier) {
                                    $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
                                        new CreateRestaurantModifierRequestData(
                                            $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                            $modifier->externalId->id
                                        )
                                    );

                                    $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                    $restaurantModifier = $restaurantModifierBuilder
                                        ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                        ->setWelcomeGroupModifierId($modifier->id);

                                    $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());
                                    $restaurantModifier = $restaurantModifier->setId($createdRestaurantModifier->id);
                                } else {
                                    $restaurantModifierResponse = $welcomeGroupConnector->updateRestaurantModifier(
                                        new EditRestaurantModifierRequestData(
                                            $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                            $modifier->externalId->id
                                        ),
                                        $restaurantModifier->id
                                    );

                                    $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                                    $restaurantModifier = $restaurantModifierBuilder
                                        ->setWelcomeGroupRestaurantId($itemContext->organizationSetting->welcomeGroupRestaurantId)
                                        ->setWelcomeGroupModifierId($modifier->id);

                                    $createdRestaurantModifier = $welcomeGroupRestaurantModifierRepository->update($restaurantModifier->build());
                                    $restaurantModifier = $restaurantModifier->setId($createdRestaurantModifier->id);

                                }

                                // Получаем стоимость товара. В теории нам кажется, что прайса может быть больше чем 1, но
                                // ни айка (CHICKO), ни WG не умеют работать с разными размерами товаров и их прайсами
                                $itemPrice = $item->prices->first();

                                if (! $itemPrice) {
                                    throw new PriceNotLoadedException(sprintf('Price not loaded for item %s', $item->id->id));
                                }

                                // Ищем во внутренней БД связь модификатора с блюдом
                                $foodModifier = $welcomeGroupFoodModifierRepository->findByInternalFoodAndModifierIds($itemContext->food->id, $modifier->id);

                                // Если не нашли связь модификатора с блюдом, то создаём её в WG и во внутренней базе
                                if (! $foodModifier) {
                                    $createFoodModifierResponse = $welcomeGroupConnector->createFoodModifier(
                                        new CreateFoodModifierRequestData(
                                            $itemContext->food->externalId->id,
                                            $modifier->externalId->id,
                                            $item->weight,
                                            $itemPrice->price ?? 0,
                                        ),
                                    );

                                    $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                        $createFoodModifierResponse->toDomainEntity(),
                                    );
                                    $foodModifierBuilder = $foodModifierBuilder
                                        ->setInternalModifierId($modifier->id)
                                        ->setInternalFoodId($itemContext->food->id);

                                    // Процесс создания связи во внутренней базе
                                    $welcomeGroupFoodModifierRepository->save($foodModifierBuilder->build());
                                } else {
                                    $createFoodModifierResponse = $welcomeGroupConnector->updateFoodModifier(
                                        new EditFoodModifierRequestData(
                                            $itemContext->food->externalId->id,
                                            $modifier->externalId->id,
                                            $item->weight,
                                            0,
                                            $itemPrice->price ?? 0,
                                            0
                                        ),
                                        $foodModifier->id
                                    );

                                    $foodModifierBuilder = FoodModifierBuilder::fromExisted(
                                        $createFoodModifierResponse->toDomainEntity(),
                                    );
                                    $foodModifierBuilder = $foodModifierBuilder
                                        ->setInternalModifierId($modifier->id)
                                        ->setInternalFoodId($itemContext->food->id)
                                        ->setId($foodModifier->id);

                                    // Процесс создания связи во внутренней базе
                                    $welcomeGroupFoodModifierRepository->update($foodModifierBuilder->build());
                                }
                            }
                        );
                    });
                } elseif ($missingCount < 0) {
                    // Кейс выявил, что есть лишние типы модификаторов, необходимо удалить то количество, которое указано в $count
                    // Получаем коллекцию из последних $excessCount элементов для удаления
                    $modifiersTypesToDelete = $existedModifierTypeCollection->slice($missingCount);

                    // Удаляем лишние модификаторы
                    $modifiersTypesToDelete->each(static function (WelcomeGroupModifierType $modifierType) use (
                        $welcomeGroupRestaurantModifierRepository,
                        $welcomeGroupConnector,
                        $itemContext

                    ) {
                        $modifierType
                            ->modifiers
                            ->each(static function (WelcomeGroupModifier $modifier) use (
                                $welcomeGroupRestaurantModifierRepository,
                                $welcomeGroupConnector,
                                $itemContext
                            ) {
                                $restaurantModifier = $welcomeGroupRestaurantModifierRepository->findByInternalRestaurantAndModifierId($itemContext->organizationSetting->id, new IntegerId($modifier->id));
                                // При отсутствии связи создаём её в WG. Кстати обновления связи не делал, пока показалось, что не требуется
                                $updatedRestaurantModifier = $welcomeGroupConnector->updateRestaurantModifier(
                                    new EditRestaurantModifierRequestData(
                                        $itemContext->organizationSetting->welcomeGroupRestaurantId->id,
                                        $modifier->external_id,
                                        'blocked'
                                    ),
                                    new IntegerId($restaurantModifier->externalId->id),
                                );

                                WelcomeGroupRestaurantFood::query()->find($restaurantModifier->id->id)->delete();
                                $modifier->delete();
                            });

                        // Удаляем тип модификатора из системы
                        $modifierType->delete();
                    });

                    // Оставшиеся модификаторы
                    $remainingModifierTypes = $existedModifierTypeCollection->slice(0, $existedModifierTypeCollection->count() - abs($missingCount));

                    // Обновляем оставшиеся модификаторы
                    $remainingModifierTypes->each(static function (WelcomeGroupModifierType $modifierType) use ($welcomeGroupConnector, $modifierGroup, $maxQuantity, $welcomeGroupModifierTypeRepository) {
                        $response = $welcomeGroupConnector
                            ->updateModifierType(
                                new EditModifierTypeRequestData(
                                    $modifierGroup->name,
                                    ModifierTypeBehaviour::fromValue($maxQuantity)->value
                                ),
                                new IntegerId($modifierType->external_id)
                            );

                        $modifierBuilder = ModifierTypeBuilder::fromExisted($response->toDomainEntity())
                            ->setId(new IntegerId($modifierType->id))
                            ->setIikoMenuItemModifierGroupId($modifierGroup->id);

                        $welcomeGroupModifierTypeRepository->save($modifierBuilder->build());
                    });
                }
            }
        }
    }

    private function fetchItemContext(
        IikoMenuRepositoryInterface $menuRepository,
        OrganizationSettingRepositoryInterface $orgRepository,
        WelcomeGroupFoodCategoryRepositoryInterface $categoryRepository,
        WelcomeGroupFoodRepositoryInterface $foodRepository,
        WelcomeGroupRestaurantFoodRepository $restaurantFoodRepository,
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
        $restaurantFood = $restaurantFoodRepository->findByInternalFoodAndRestaurantId($food->id, $organizationSetting->id);

        return new ItemContext($item, $itemBuilder, $food, $organizationSetting, $category, $foodRepository, $restaurantFood);
    }
}

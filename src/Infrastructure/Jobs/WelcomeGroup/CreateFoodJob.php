<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Application\WelcomeGroup\Builders\FoodBuilder;
use Application\WelcomeGroup\Builders\FoodModifierBuilder;
use Application\WelcomeGroup\Builders\ModifierBuilder;
use Application\WelcomeGroup\Builders\RestaurantFoodBuilder;
use Application\WelcomeGroup\Builders\RestaurantModifierBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Exceptions\PriceNotLoadedException;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Enums\ModifierTypeBehaviour;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\CreateFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierRequestData;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories\WelcomeGroupRestaurantFoodRepository;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class CreateFoodJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly Item $item)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

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
        WelcomeGroupRestaurantFoodRepository $welcomeGroupRestaurantFoodRepository,
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository,
    ): void {
        $iikoItem = $this->item;

        $iikoMenuItemSizes = $iikoMenuItemSizeRepository->findForWithAllRelations($iikoItem);

        $iikoItemBuilder = ItemBuilder::fromExisted($iikoItem);
        $iikoItemBuilder = $iikoItemBuilder
            ->setItemSizes($iikoMenuItemSizes)
            ->build();

        $foodCategory = $welcomeGroupFoodCategoryRepository->findByIikoMenuItemGroupId($iikoItemBuilder->itemGroupId);

        if (! $foodCategory) {
            return;
        }

        $iikoMenu = $iikoMenuRepository->findforItem($iikoItemBuilder);

        if (! $iikoMenu) {
            return;
        }

        $organizationSetting = $organizationSettingRepository->findById($iikoMenu->organizationSettingId);

        if (! $organizationSetting) {
            return;
        }

        $foodBuilder = FoodBuilder::fromIikoItem($iikoItemBuilder)
            ->setWorkshopId($organizationSetting->welcomeGroupDefaultWorkshopId)
            ->setInternalFoodCategoryId($foodCategory->id)
            ->setExternalFoodCategoryId($foodCategory->externalId);

        $foodRequest = $foodBuilder->build();

        $foodResponse = $welcomeGroupConnector->createFood(
            new CreateFoodRequestData(
                $foodRequest->externalFoodCategoryId->id,
                $foodRequest->workshopId->id,
                $foodRequest->name,
                $foodRequest->description,
                $foodRequest->weight,
                $foodRequest->caloricity,
                $foodRequest->price,
            ),
        );

        $foodBuilder = $foodBuilder->setExternalId(new IntegerId($foodResponse->id));

        $createdFood = $welcomeGroupFoodRepository->save($foodBuilder->build());

        $restaurantFoodResponse = $welcomeGroupConnector->createRestaurantFood(
            new CreateRestaurantFoodRequestData(
                $organizationSetting->welcomeGroupRestaurantId->id,
                $createdFood->id->id,
            )
        );

        $restaurantFoodBuilder = RestaurantFoodBuilder::fromExisted(
            $restaurantFoodResponse->toDomainEntity()
        )
            ->setWelcomeGroupFoodId($createdFood->id)
            ->setWelcomeGroupRestaurantId($organizationSetting->welcomeGroupRestaurantId)
            ->setExternalId(new IntegerId($restaurantFoodResponse->id));

        $createdRestaurantFood = $welcomeGroupRestaurantFoodRepository->save($restaurantFoodBuilder->build());

        $restaurantFood = $restaurantFoodBuilder
            ->setId($createdRestaurantFood->id)
            ->build();

        $food = $foodBuilder
            ->setId($createdFood->id)
            ->build();

        $iikoMenuItemSizes->each(function (ItemSize $itemSize) use (
            $welcomeGroupFoodModifierRepository,
            $food,
            $welcomeGroupModifierRepository,
            $welcomeGroupModifierTypeRepository,
            $welcomeGroupConnector,
            $organizationSetting,
            $welcomeGroupRestaurantModifierRepository
        ): void {
            $this->handleModifierGroups(
                $food,
                $itemSize->itemModifierGroups,
                $welcomeGroupConnector,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupModifierRepository,
                $welcomeGroupFoodModifierRepository,
                $organizationSetting,
                $welcomeGroupRestaurantModifierRepository,
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
        OrganizationSetting $organizationSetting,
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository
    ): void {
        $modifierGroupCollection->each(
            function (ItemModifierGroup $itemModifierGroup) use (
                $welcomeGroupFoodModifierRepository,
                $food,
                $welcomeGroupModifierRepository,
                $welcomeGroupModifierTypeRepository,
                $welcomeGroupConnector,
                $organizationSetting,
                $welcomeGroupRestaurantModifierRepository
            ): void {
                $this->handleModifierGroup(
                    $food,
                    $itemModifierGroup,
                    $welcomeGroupConnector,
                    $welcomeGroupModifierTypeRepository,
                    $welcomeGroupModifierRepository,
                    $welcomeGroupFoodModifierRepository,
                    $organizationSetting,
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
        OrganizationSetting $organizationSetting,
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository
    ): void {
        $maxQuantity = $modifierGroup->maxQuantity;

        for ($i = 0; $i < $maxQuantity; $i++) {
            $modifierTypeResponse = $welcomeGroupConnector->createModifierType(
                new CreateModifierTypeRequestData(
                    $modifierGroup->name,
                    ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                ),
            );

            $modifierType = $welcomeGroupModifierTypeRepository->save($modifierTypeResponse->toDomainEntity());

            $modifierGroup->items->each(
                static function (Item $item) use (
                    $welcomeGroupFoodModifierRepository,
                    $food,
                    $modifierType,
                    $welcomeGroupModifierRepository,
                    $modifierTypeResponse,
                    $welcomeGroupConnector,
                    $organizationSetting,
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
                            $organizationSetting->welcomeGroupRestaurantId->id,
                            $createdModifier->externalId->id
                        )
                    );

                    $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
                    $restaurantModifier = $restaurantModifierBuilder
                        ->setWelcomeGroupRestaurantId($organizationSetting->welcomeGroupRestaurantId)
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
    }
}

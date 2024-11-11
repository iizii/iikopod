<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Application\WelcomeGroup\Builders\FoodBuilder;
use Application\WelcomeGroup\Builders\FoodModifierBuilder;
use Application\WelcomeGroup\Builders\ModifierBuilder;
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
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\CreateFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
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

        $response = $welcomeGroupConnector->createFood(
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

        $foodBuilder = $foodBuilder->setExternalId(new IntegerId($response->id));

        $createdFood = $welcomeGroupFoodRepository->save($foodBuilder->build());

        $food = $foodBuilder
            ->setId($createdFood->id)
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
                    $welcomeGroupConnector
                ) {
                    $modifierResponse = $welcomeGroupConnector->createModifier(
                        new CreateModifierRequestData(
                            $item->name,
                            $modifierTypeResponse->id,
                        ),
                    );

                    $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity());
                    $modifier = $modifierBuilder
                        ->setInternalModifierTypeId($modifierType->id)
                        ->build();

                    $createdModifier = $welcomeGroupModifierRepository->save($modifier);

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

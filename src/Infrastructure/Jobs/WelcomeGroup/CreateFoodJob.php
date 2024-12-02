<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Application\WelcomeGroup\Builders\FoodBuilder;
use Application\WelcomeGroup\Builders\RestaurantFoodBuilder;
use Carbon\CarbonImmutable;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Enums\ModifierTypeBehaviour;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantFoodRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\CreateFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodRequestData;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class CreateFoodJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
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
        QueueingDispatcher $dispatcher,
        CarbonImmutable $now,
        IikoMenuRepositoryInterface $iikoMenuRepository,
        IikoMenuItemSizeRepositoryInterface $iikoMenuItemSizeRepository,
        OrganizationSettingRepositoryInterface $organizationSettingRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodCategoryRepositoryInterface $welcomeGroupFoodCategoryRepository,
        WelcomeGroupFoodRepositoryInterface $welcomeGroupFoodRepository,
        WelcomeGroupRestaurantFoodRepositoryInterface $welcomeGroupRestaurantFoodRepository,
    ): void {
        $iikoItem = $this->item;

        $iikoMenuItemSizes = $iikoMenuItemSizeRepository->findForWithAllRelations($iikoItem);

        $iikoItemBuilder = ItemBuilder::fromExisted($iikoItem);
        $iikoItemBuilder = $iikoItemBuilder
            ->setItemSizes($iikoMenuItemSizes)
            ->build();

        $foodCategory = $welcomeGroupFoodCategoryRepository->findByIikoMenuItemGroupId($iikoItemBuilder->itemGroupId);

        if (!$foodCategory) {
            $this->release($now->addMinute());

            return;
        }

        $iikoMenu = $iikoMenuRepository->findforItem($iikoItemBuilder);

        if (!$iikoMenu) {
            throw new \RuntimeException('Iiko menu not found');
        }

        $organizationSetting = $organizationSettingRepository->findById($iikoMenu->organizationSettingId);

        if (!$organizationSetting) {
            throw new \RuntimeException('Organization Setting not found');
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
            ),
        );

        $restaurantFoodBuilder = RestaurantFoodBuilder::fromExisted($restaurantFoodResponse->toDomainEntity());
        $restaurantFoodBuilder = $restaurantFoodBuilder
                ->setWelcomeGroupFoodId($createdFood->id)
                ->setWelcomeGroupRestaurantId($organizationSetting->welcomeGroupRestaurantId);

        $createdRestaurantFood = $welcomeGroupRestaurantFoodRepository->save($restaurantFoodBuilder->build());

        $restaurantFood = $restaurantFoodBuilder
            ->setId($createdRestaurantFood->id)
            ->build();

        $food = $foodBuilder
            ->setId($createdFood->id)
            ->build();

        $iikoMenuItemSizes->each(function (ItemSize $itemSize) use ($dispatcher, $food, $organizationSetting): void {
            $itemSize->itemModifierGroups->each(
                function (ItemModifierGroup $itemModifierGroup) use ($dispatcher, $food, $organizationSetting): void {
                    $this->handleModifierGroup($dispatcher, $food, $itemModifierGroup, $organizationSetting);
                },
            );
        });
    }

    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): int
    {
        return 60;
    }

    private function handleModifierGroup(
        QueueingDispatcher $dispatcher,
        Food $food,
        ItemModifierGroup $modifierGroup,
        OrganizationSetting $organizationSetting,
    ): void {
        $maxQuantity = $modifierGroup->maxQuantity;

        for ($i = 0; $i < $maxQuantity; $i++) {
            $dispatcher->dispatch(
                new CreateModifierTypeJob(
                    $food,
                    new CreateModifierTypeRequestData(
                        $modifierGroup->name,
                        ModifierTypeBehaviour::fromValue($maxQuantity)->value,
                    ),
                    $modifierGroup,
                    $organizationSetting,
                ),
            );
        }
    }
}

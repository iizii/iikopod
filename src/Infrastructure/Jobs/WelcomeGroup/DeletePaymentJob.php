<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\Iiko\Builders\ItemBuilder;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\OrganizationSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierRequestData;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantModifier;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class DeletePaymentJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public readonly IikoMenuItem $item)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    public function handle(
        IikoMenuRepositoryInterface $iikoMenuRepository,
        IikoMenuItemSizeRepositoryInterface $iikoMenuItemSizeRepository,
        OrganizationSettingRepositoryInterface $organizationSettingRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
    ): void {
        $item = $this->item::toDomainEntity($this->item);
        $itemSizes = $iikoMenuItemSizeRepository->findForWithAllRelations($item);

        $builtItem = ItemBuilder::fromExisted($item)
            ->setItemSizes($itemSizes)
            ->build();

        $iikoMenu = $iikoMenuRepository->findforItem($builtItem);
        if (! $iikoMenu) {
            throw new \RuntimeException('Iiko menu not found');
        }

        $organizationSetting = $organizationSettingRepository->findById($iikoMenu->organizationSettingId);
        if (! $organizationSetting) {
            throw new \RuntimeException('Organization Setting not found');
        }

        $this->blockModifiers($organizationSetting, $welcomeGroupConnector);
        $this->blockFood($organizationSetting, $welcomeGroupConnector);
    }

    public function tries(): int
    {
        return 3;
    }

    public function backoff(): int
    {
        return 60;
    }

    private function blockModifiers(
        OrganizationSetting $organizationSetting,
        WelcomeGroupConnectorInterface $connector
    ): void {
        $this->item
            ->itemSizes()
            ->each(static function (IikoMenuItemSize $itemSize) use ($organizationSetting, $connector) {
                $itemSize
                    ->itemModifierGroups()
                    ->each(static function (IikoMenuItemModifierGroup $group) use ($organizationSetting, $connector) {
                        $group
                            ->items()
                            ->each(static function (IikoMenuItemModifierItem $itemModifier) use ($organizationSetting, $connector) {
                                $modifier = WelcomeGroupModifier::query()
                                    ->where('iiko_menu_item_modifier_item_id', $itemModifier->id)
                                    ->first();

                                if (! $modifier) {
                                    return; // Или лог, если нужно
                                }

                                $restaurantModifier = WelcomeGroupRestaurantModifier::query()
                                    ->where('restaurant_id', $organizationSetting->id->id)
                                    ->where('welcome_group_modifier_id', $modifier->external_id)
                                    ->first();

                                if (! $restaurantModifier) {
                                    return; // Или лог
                                }

                                $connector->updateRestaurantModifier(
                                    new EditRestaurantModifierRequestData(
                                        (int)$organizationSetting->welcomeGroupRestaurantId->id,
                                        $modifier->external_id,
                                        'blocked'
                                    ),
                                    new IntegerId($restaurantModifier->id)
                                );
                            });
                    });
            });
    }

    private function blockFood(
        OrganizationSetting $organizationSetting,
        WelcomeGroupConnectorInterface $connector
    ): void {
        $restaurantFood = WelcomeGroupRestaurantFood::query()
            ->where('welcome_group_restaurant_id', $organizationSetting->welcomeGroupRestaurantId->id)
            ->where('welcome_group_food_id', $this->item->food->id)
            ->first();

        if (! $restaurantFood) {
            throw new \RuntimeException('Restaurant Food not found');
        }

        $connector->updateRestaurantFood(
            new EditRestaurantFoodRequestData(
                $restaurantFood->restaurant_id,
                $restaurantFood->food_id,
                'blocked'
            ),
            new IntegerId($restaurantFood->external_id)
        );
    }
}

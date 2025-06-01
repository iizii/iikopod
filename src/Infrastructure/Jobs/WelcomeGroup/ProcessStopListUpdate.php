<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantFoodRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodRequestData;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantFood;
use Shared\Domain\ValueObjects\IntegerId;

final class ProcessStopListUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly array $stopListProductIds,
        private readonly int $organizationId,
    ) {}

    public function handle(
        IikoMenuRepositoryInterface $iikoMenuRepository,
        WelcomeGroupRestaurantFoodRepositoryInterface $restaurantFoodRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
    ): void {
        $menu = $iikoMenuRepository->getAllByInternalOrganizationIdWithItemGroups(new IntegerId($this->organizationId));

        if (! $menu) {
            logger()->channel('stop_list_update')->error('Меню ресторана не найдено', [
                'restaurant' => $this->organizationId,
            ]);

            return;
        }

        $menu->each(function (IikoMenu $iikoMenu) {
            $iikoMenu->itemGroups->each(function (IikoMenuItemGroup $iikoMenuItemGroup) {
                // Обрабатываем товары в стоп-листе
                $this->processItems(
                    $iikoMenuItemGroup->items()->whereIn('external_id', $this->stopListProductIds)->get(),
                    true
                );

                // Обрабатываем товары не в стоп-листе
                $this->processItems(
                    $iikoMenuItemGroup->items()->whereNotIn('external_id', $this->stopListProductIds)->get(),
                    false
                );
            });
        });
    }

    protected function processItems(Collection $items, bool $isHidden): void
    {
        $items->each(function (IikoMenuItem $iikoMenuItem) use ($isHidden) {
            $iikoMenuItem->is_hidden = $isHidden;
            $iikoMenuItem->saveQuietly();

            $this->updateWelcomeGroupStatus($iikoMenuItem, $isHidden);
        });
    }

    protected function updateWelcomeGroupStatus(IikoMenuItem $item, bool $isHidden): void
    {
        $welcomeGroupFood = $item->load('food')->food;
        if (! $welcomeGroupFood) {
            return;
        }

        $restaurantFood = WelcomeGroupRestaurantFood::query()
            ->where('food_id', $welcomeGroupFood->id)
            ->first();

        if (! $restaurantFood) {
            return;
        }

        $restaurantFood->status = $isHidden ? 'stopped' : 'active';
        $restaurantFood->save();

        app(WelcomeGroupConnectorInterface::class)->updateRestaurantFood(
            new EditRestaurantFoodRequestData(
                $restaurantFood->restaurant_id,
                $restaurantFood->food_id,
                $restaurantFood->status
            ),
            new IntegerId($restaurantFood->external_id)
        );
    }
}

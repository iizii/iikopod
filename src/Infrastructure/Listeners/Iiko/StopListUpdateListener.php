<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\Iiko;

use Application\Iiko\Events\StopListUpdateEvent;
use Domain\Iiko\Exceptions\RestaurantNotFoundException;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\IIkoConnector;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;
use Infrastructure\Persistence\Eloquent\Settings\Repositories\OrganizationSettingRepository;
use Mockery\Exception;
use Shared\Domain\ValueObjects\StringId;

final class StopListUpdateListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly IikoConnector $connector,
        private readonly IikoAuthenticator $authenticator,
        private readonly OrganizationSettingRepository $settingRepository,
        private readonly IikoMenuItemRepositoryInterface $iikoMenuItemRepository,
        private readonly IikoMenuRepositoryInterface $iikoMenuRepository,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(StopListUpdateEvent $event): void
    {
        $data = $event->eventData;

        $restaurant = $this->settingRepository->findByIIkoId(new StringId($data->organizationId));

        if (! $restaurant) {

            logger()
                ->channel('stop_list_update')
                ->debug('Ошибка при обновлении стоплиста:', [$data]);
            throw new RestaurantNotFoundException(__(
                "Не удалось найти ресторан по iiko id :iikoId.
                \n В связи с этим для ресторана не был обработан вебхук для постановки блюд в стоп-лист ресторана: :data",
                ['iikoId' => $data->organizationId, 'data' => json_encode($data)]
            ));
        }

        try {
            $stopListResponse = $this
                ->connector
                ->getStopLists(
                    $data->organizationId,
                    $this->authenticator->getAuthToken($restaurant->iikoApiKey)
                );
        } catch (ConnectionException|RequestException $e) {
            logger()
                ->channel('stop_list_update')
                ->info(
                    'Не удачная попытка получить стоп-листы',
                    [
                        'data' => $data,
                    ]
                );

            throw new Exception('Не удалось получить стоп-листы. Проверяйте логи');
        }

        if ($stopListResponse->isEmpty()) {
            logger()->channel('stop_list_update')->warning('Получен пустой стоп-лист', ['data' => $data]);

            return;
        }

        $menu = $this
            ->iikoMenuRepository
            ->getAllByInternalOrganizationIdWithItemGroups($restaurant->id);

        if (! $menu) {
            logger()
                ->channel('stop_list_update')
                ->error('Меню ресторана не найдено', ['restaurant' => $restaurant->id]);

            return;
        }
        $stopListProductIds = $stopListResponse->pluck('productId');
        $menu->each(static function (IikoMenu $iikoMenu) use ($stopListProductIds) {
            $iikoMenu
                ->itemGroups
                ->each(static function (IikoMenuItemGroup $iikoMenuItemGroup) use ($stopListProductIds) {
                    $iikoMenuItemGroup
                        ->items()
                        ->whereIn('product_id', $stopListProductIds)
                        ->each(static function (IikoMenuItem $iikoMenuItem) {
                            $iikoMenuItem->is_hidden = true;
                            $iikoMenuItem->save();
                        });
                    $iikoMenuItemGroup
                        ->items()
                        ->whereNotIn('product_id', $stopListProductIds)
                        ->each(static function (IikoMenuItem $iikoMenuItem) {
                            $iikoMenuItem->is_hidden = false;
                            $iikoMenuItem->save();
                        });
                });
        });
    }
}

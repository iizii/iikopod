<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\Iiko;

use Application\Iiko\Events\StopListUpdateEvent;
use Domain\Iiko\Exceptions\RestaurantNotFoundException;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListResponseData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Jobs\WelcomeGroup\ProcessStopListUpdate;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\StringId;

final class StopListUpdateListener
{
    public function __construct(
        private readonly IikoConnectorInterface $connector,
        private readonly IikoAuthenticator $authenticator,
        private readonly OrganizationSettingRepositoryInterface $settingRepository,
    ) {}

    public function handle(StopListUpdateEvent $event): void
    {
        $data = $event->eventData;

        $restaurant = $this->settingRepository->findByIIkoId(new StringId($data->organizationId));

        if (! $restaurant) {
            logger()->channel('stop_list_update')->debug('Ошибка при обновлении стоплиста:', [$data]);
            throw new RestaurantNotFoundException(__(
                'Не удалось найти ресторан по iiko id :iikoId.',
                ['iikoId' => $data->organizationId]
            ));
        }

        try {
            $stopListResponse = $this->connector->getStopLists(
                $data->organizationId,
                $this->authenticator->getAuthToken($restaurant->iikoApiKey)
            );
        } catch (ConnectionException|RequestException $e) {
            logger()->channel('stop_list_update')->info('Неудачная попытка получить стоп-листы', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Не удалось получить стоп-листы. Проверяйте логи');
        }

        if ($stopListResponse->isEmpty()) {
            logger()->channel('stop_list_update')->warning('Получен пустой стоп-лист', ['data' => $data]);

            return;
        }

        $stopListProductIds = $stopListResponse
            ->map(static fn (GetStopListResponseData $item) => $item->productId)
            ->values()
            ->all();

        // Диспатчим job в очередь
        ProcessStopListUpdate::dispatch(
            $stopListProductIds,
            $restaurant->id->id
        )->onQueue(Queue::STOP_LIST->value);
    }
}

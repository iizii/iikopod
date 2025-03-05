<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup\Order;

use Application\Orders\Builders\OrderBuilder;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Settings\Exceptions\OrganizationNotFoundException;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\WelcomeGroup\Enums\OrderSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\CreateAddressRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\CreateClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneRequestData;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class CreateOrderJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Order $order)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     *
     * @throws OrganizationNotFoundException
     */
    public function handle(
        OrderRepositoryInterface $orderRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ): void {
        $order = $this->order;

        $organizationSettings = $organizationSettingRepository->findById($order->organizationId);

        if (! $organizationSettings) {
            throw new OrganizationNotFoundException(
                sprintf(
                    'Не найден ресторан %s при создании заказа %s в Welcome Group',
                    $order->organizationId,
                    $order->id,
                ),
            );
        }

        $orderPhone = $order->customer->phone;

        try {
            $phone = $welcomeGroupConnector->findPhone(new FindPhoneRequestData($orderPhone))->first();
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При создании заказа %s в Welcome Group произошла ошибка при попытке найти телефон клиента %s, ошибка: %s',
                    $order->id->id,
                    $orderPhone,
                    $e->getMessage(),
                ),
            );
        }

        if (! $phone) {
            try {
                $phone = $welcomeGroupConnector->createPhone(
                    new CreatePhoneRequestData($orderPhone),
                );
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    sprintf(
                        'При создании заказа %s в Welcome Group произошла ошибка при попытке создать телефон клиента %s, ошибка: %s',
                        $order->id->id,
                        $orderPhone,
                        $e->getMessage(),
                    ),
                );
            }
        }

        try {
            $client = $welcomeGroupConnector->createClient(
                new CreateClientRequestData(
                    $order->customer->name ?? 'Не указано',
                ),
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При создании заказа %s в Welcome Group произошла ошибка при попытке создать клиента %s, ошибка: %s',
                    $order->id->id,
                    $orderPhone,
                    $e->getMessage(),
                ),
            );
        }

        try {

            $address = $order->deliveryPoint;

            $deliveryAddress = $welcomeGroupConnector->createAddress(
                new CreateAddressRequestData(
                    $address->address->street->city->name,
                    $address->address->street->name,
                    $address->address->house,
                    $address->address->building,
                    $address->address->floor,
                    $address->address->flat,
                    $address->address->entrance,
                    $address->coordinates->latitude,
                    $address->coordinates->longitude,
                    $address->comment,
                ),
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При создании заказа %s в Welcome Group произошла ошибка при попытке создать клиента %s, ошибка: %s',
                    $order->id->id,
                    $orderPhone,
                    $e->getMessage(),
                ),
            );
        }

        try {
            $welcomeGroupRestaurant = $welcomeGroupConnector->getRestaurant(
                $organizationSettings->welcomeGroupRestaurantId,
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При создании заказа %s в Welcome Group произошла ошибка при попытке получить ресторан %s, ошибка: %s',
                    $order->id->id,
                    $organizationSettings->welcomeGroupRestaurantId,
                    $e->getMessage(),
                ),
            );
        }

        $totalCompleteOrderTime = now()->addSeconds(
            $welcomeGroupRestaurant->timeWaitingCooking
            + $welcomeGroupRestaurant->timeCooking
            + $welcomeGroupRestaurant->timeWaitingDelivering
            + $welcomeGroupRestaurant->timeDelivering,
        );
        $isPreorder = $order
            ->completeBefore
            ->greaterThan($totalCompleteOrderTime);

        $timePreorder = null;

        if ($isPreorder) {
            $timePreorder = $order
                ->completeBefore
                ->toRfc7231String();
            //                ->subSeconds(
            //                    ($welcomeGroupRestaurant->timeWaitingCooking
            //                        + $welcomeGroupRestaurant->timeCooking
            //                        + $welcomeGroupRestaurant->timeWaitingDelivering
            //                        + $welcomeGroupRestaurant->timeDelivering)
            //                )->toRfc7231String();
        }

        try {
            $response = $welcomeGroupConnector->createOrder(
                new CreateOrderRequestData(
                    $organizationSettings->welcomeGroupRestaurantId->id,
                    $client->id,
                    $phone->id,
                    $deliveryAddress->id,
                    [],
                    OrderStatus::toWelcomeGroupStatus($order->status),
                    100,
                    0,
                    $order->comment,
                    OrderSource::TEST->value,
                    $isPreorder,
                    $timePreorder,
                ),
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При создании заказа %s в Welcome Group произошла ошибка, ошибка: %s',
                    $order->id->id,
                    $e->getMessage(),
                ),
            );
        }

        $sentOrder = OrderBuilder::fromExisted($order);
        $sentOrder = $sentOrder->setWelcomeGroupExternalId(new IntegerId($response->id));

        $orderRepository->update($sentOrder->build());
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
}

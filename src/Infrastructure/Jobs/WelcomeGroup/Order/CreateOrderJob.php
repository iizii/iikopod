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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
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
     * @throws RequestException
     * @throws ConnectionException
     */
    public function handle(
        OrderRepositoryInterface $orderRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ): void {
        $order = $this->order;

        $organizationSettings = $organizationSettingRepository->findById($order->organizationId);

        if (! $organizationSettings) {
            throw new OrganizationNotFoundException();
        }

        $orderPhone = $order->customer->phone;

        $phone = $welcomeGroupConnector->findPhone(new FindPhoneRequestData($orderPhone))->first();

        if (! $phone) {
            $phone = $welcomeGroupConnector->createPhone(
                new CreatePhoneRequestData($orderPhone),
            );
        }

        $client = $welcomeGroupConnector->createClient(
            new CreateClientRequestData(
                $order->customer->name ?? 'Не указано',
            ),
        );

        $welcomeGroupRestaurant = $welcomeGroupConnector
            ->getRestaurant($organizationSettings->welcomeGroupRestaurantId);
        $totalCompleteOrderTime = now()->addSeconds($welcomeGroupRestaurant->timeWaitingCooking
            + $welcomeGroupRestaurant->timeCooking
            + $welcomeGroupRestaurant->timeWaitingDelivering
            + $welcomeGroupRestaurant->timeDelivering);
        $isPreorder = $order
            ->completeBefore
            ->greaterThan($totalCompleteOrderTime);

        $response = $welcomeGroupConnector->createOrder(
            new CreateOrderRequestData(
                $organizationSettings->welcomeGroupRestaurantId->id,
                $client->id,
                $phone->id,
                1,
                [],
                OrderStatus::toWelcomeGroupStatus($order->status),
                100,
                0,
                $order->comment,
                OrderSource::TEST->value,
                $isPreorder,
            ),
        );

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

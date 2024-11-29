<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup\Order;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Exceptions\OrderNotFoundException;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\WelcomeGroup\Enums\OrderPaymentStatus;
use Domain\WelcomeGroup\Enums\OrderPaymentType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Queue\Queue;

final class CreateOrderPaymentJob implements ShouldQueue
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
     * @throws RequestException
     * @throws ConnectionException|OrderNotFoundException
     */
    public function handle(
        OrderRepositoryInterface $orderRepository,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
    ): void {
        $order = $orderRepository->findByIikoId($this->order->iikoExternalId);

        if (! $order) {
            throw new OrderNotFoundException();
        }

        $welcomeGroupConnector->createPayment(
            new CreateOrderPaymentRequestData(
                $order->welcomeGroupExternalId->id,
                OrderPaymentStatus::FINISHED,
                OrderPaymentType::CARD,
                $this->order->payment->amount,
            ),
        );
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

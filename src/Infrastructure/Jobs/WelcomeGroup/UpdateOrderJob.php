<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Queue\Queue;

final class UpdateOrderJob implements ShouldBeUnique, ShouldQueue
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
     * @throws ConnectionException
     */
    public function handle(WelcomeGroupConnectorInterface $welcomeGroupConnector): void
    {
        $order = $this->order;

        $welcomeGroupConnector->updateOrder(
            $order->welcomeGroupExternalId,
            new UpdateOrderRequestData(
                OrderStatus::toWelcomeGroupStatus($order->status),
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

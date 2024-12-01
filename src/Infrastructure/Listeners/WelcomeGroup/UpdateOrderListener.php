<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Orders\Events\OrderUpdatedEvent;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Infrastructure\Jobs\WelcomeGroup\UpdateOrderJob;

final readonly class UpdateOrderListener
{
    public function __construct(private QueueingDispatcher $dispatcher) {}

    public function handle(OrderUpdatedEvent $orderCreatedEvent): void
    {
        $this->dispatcher->dispatch(new UpdateOrderJob($orderCreatedEvent->order));
    }
}

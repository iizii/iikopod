<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Application\WelcomeGroup\Services\OrderSender;
use Domain\Orders\Events\OrderCreatedEvent;

final readonly class SendOrderListener
{
    public function __construct(private OrderSender $orderSender) {}

    public function handle(OrderCreatedEvent $orderCreatedEvent): void
    {
        $this->orderSender->send($orderCreatedEvent->order, $orderCreatedEvent->sourceKey);
    }
}

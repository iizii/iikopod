<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\Iiko;

use Application\Iiko\Events\DeliveryOrderUpdateEvent;
use Application\Iiko\Services\Order\CreateOrderFromWebhook;

final readonly class DeliveryOrderUpdateListener
{
    /**
     * Create the event listener.
     */
    public function __construct(private CreateOrderFromWebhook $createOrderFromWebhook)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DeliveryOrderUpdateEvent $event): void
    {
        $this->createOrderFromWebhook->handle($event->eventData);
    }
}

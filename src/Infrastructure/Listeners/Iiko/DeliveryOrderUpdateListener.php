<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\Iiko;

use Application\Iiko\Events\DeliveryOrderUpdateEvent;

final class DeliveryOrderUpdateListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DeliveryOrderUpdateEvent $event): void
    {
        logger()->channel('delivery_order_update')->info('Listener data', [$event]);
    }
}

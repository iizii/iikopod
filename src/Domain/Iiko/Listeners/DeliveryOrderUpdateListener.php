<?php

declare(strict_types=1);

namespace Domain\Iiko\Listeners;

use Domain\Iiko\Events\DeliveryOrderUpdate;

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
    public function handle(DeliveryOrderUpdate $event): void
    {
        logger()->channel('delivery_order_update')->info('Listener data', [$event]);
    }
}

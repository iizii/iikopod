<?php

namespace Domain\Iiko\Listeners;

use Domain\Iiko\Events\DeliveryOrderUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeliveryOrderUpdateListener
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

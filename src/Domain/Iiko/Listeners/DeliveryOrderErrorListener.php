<?php

namespace Domain\Iiko\Listeners;

use Domain\Iiko\Events\DeliveryOrderError;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeliveryOrderErrorListener
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
    public function handle(DeliveryOrderError $event): void
    {
        logger()->channel('delivery_order_error')->info('Listener data', [$event]);

    }
}

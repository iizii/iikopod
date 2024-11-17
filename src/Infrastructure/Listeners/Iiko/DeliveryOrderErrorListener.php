<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\Iiko;

use Application\Iiko\Events\DeliveryOrderErrorEvent;

final class DeliveryOrderErrorListener
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
    public function handle(DeliveryOrderErrorEvent $event): void
    {
        logger()->channel('delivery_order_error')->info('Listener data', [$event]);

    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\Iiko;

use Application\Iiko\Events\StopListUpdateEvent;

final class StopListUpdateListener
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
    public function handle(StopListUpdateEvent $event): void
    {
        logger()->channel('stop_list_update')->info('Listener data', [$event]);
    }
}

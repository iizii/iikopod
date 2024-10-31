<?php

declare(strict_types=1);

namespace Domain\Iiko\Listeners;

use Domain\Iiko\Events\StopListUpdateEvent;

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

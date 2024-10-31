<?php

namespace Domain\Iiko\Listeners;

use Domain\Iiko\Events\StopListUpdateEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StopListUpdateListener
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

<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Iiko\Events\ItemUpdatedEvent;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\UpdateFoodJob;

final readonly class SendUpdatedFoodListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private Dispatcher $dispatcher,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ItemUpdatedEvent $event): void
    {
        $this->dispatcher->dispatch(new UpdateFoodJob($event->item));
    }
}

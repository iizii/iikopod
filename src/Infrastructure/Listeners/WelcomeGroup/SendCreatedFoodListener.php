<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Iiko\Events\ItemCreatedEvent;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Infrastructure\Jobs\WelcomeGroup\CreateFoodJob;

final readonly class SendCreatedFoodListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private QueueingDispatcher $dispatcher,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ItemCreatedEvent $event): void
    {
        $this
            ->dispatcher
            ->dispatch(new CreateFoodJob($event->item));
    }
}

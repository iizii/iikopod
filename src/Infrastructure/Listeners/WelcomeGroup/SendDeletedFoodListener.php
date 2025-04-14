<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Iiko\Events\ItemCreatedEvent;
use Domain\Iiko\Events\ItemDeletedEvent;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Infrastructure\Jobs\WelcomeGroup\CreateFoodJob;
use Infrastructure\Jobs\WelcomeGroup\DeleteFoodJob;

final readonly class SendDeletedFoodListener
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
    public function handle(ItemDeletedEvent $event): void
    {
        $this->dispatcher->dispatch(new DeleteFoodJob($event->item));
    }
}

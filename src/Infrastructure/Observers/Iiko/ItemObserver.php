<?php

declare(strict_types=1);

namespace Infrastructure\Observers\Iiko;

use Domain\Iiko\Events\ItemCreatedEvent;
use Domain\Iiko\Events\ItemDeletedEvent;
use Domain\Iiko\Events\ItemUpdatedEvent;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Events\Dispatcher;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;

final readonly class ItemObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private Dispatcher $dispatcher) {}

    /**
     * Handle the IikoMenuItem "created" event.
     */
    public function created(IikoMenuItem $iikoMenuItem): void
    {
        $this
            ->dispatcher
            ->dispatch(new ItemCreatedEvent(IikoMenuItem::toDomainEntity($iikoMenuItem)));
    }

    /**
     * Handle the IikoMenuItem "updated" event.
     */
    public function updated(IikoMenuItem $iikoMenuItem): void
    {
        $this
            ->dispatcher
            ->dispatch(new ItemUpdatedEvent(IikoMenuItem::toDomainEntity($iikoMenuItem)));
    }

    /**
     * Handle the IikoMenuItem "deleted" event.
     */
    public function deleted(IikoMenuItem $iikoMenuItem): void
    {
        $this
            ->dispatcher
            ->dispatch(new ItemDeletedEvent($iikoMenuItem));
    }

    /**
     * Handle the IikoMenuItem "restored" event.
     */
    public function restored(IikoMenuItem $iikoMenuItem): void
    {
        //
    }

    /**
     * Handle the IikoMenuItem "force deleted" event.
     */
    public function forceDeleted(IikoMenuItem $iikoMenuItem): void
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Observers\Iiko;

use Domain\Iiko\Events\ItemCreatedEvent;
use Domain\Iiko\Events\ItemUpdatedEvent;
use Illuminate\Events\Dispatcher;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;

final readonly class ItemObserver
{
    public function __construct(private Dispatcher $dispatcher) {}

    /**
     * Handle the IikoMenuItem "created" event.
     */
    public function created(IikoMenuItem $iikoMenuItem): void
    {
        /* $this
             ->dispatcher
             ->dispatch(new ItemCreatedEvent(IikoMenuItem::toDomainEntity($iikoMenuItem)));*/
    }

    /**
     * Handle the IikoMenuItem "updated" event.
     */
    public function updated(IikoMenuItem $iikoMenuItem): void
    {
        /*$this
            ->dispatcher
            ->dispatch(new ItemUpdatedEvent(IikoMenuItem::toDomainEntity($iikoMenuItem)));*/
    }

    /**
     * Handle the IikoMenuItem "deleted" event.
     */
    public function deleted(IikoMenuItem $iikoMenuItem): void
    {
        //
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

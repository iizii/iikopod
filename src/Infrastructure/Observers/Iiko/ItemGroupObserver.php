<?php

declare(strict_types=1);

namespace Infrastructure\Observers\Iiko;

use Domain\Iiko\Events\ItemGroupCreatedEvent;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Events\Dispatcher;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;

final class ItemGroupObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private Dispatcher $dispatcher) {}

    /**
     * Handle the IikoMenuItemGroup "created" event.
     */
    public function created(IikoMenuItemGroup $iikoMenuItemGroup): void
    {
        $this
            ->dispatcher
            ->dispatch(new ItemGroupCreatedEvent(IikoMenuItemGroup::toDomainEntity($iikoMenuItemGroup)));
    }

    /**
     * Handle the IikoMenuItemGroup "updated" event.
     */
    public function updated(IikoMenuItemGroup $iikoMenuItemGroup): void
    {
        //
    }

    /**
     * Handle the IikoMenuItemGroup "deleted" event.
     */
    public function deleted(IikoMenuItemGroup $iikoMenuItemGroup): void
    {
        //
    }

    /**
     * Handle the IikoMenuItemGroup "restored" event.
     */
    public function restored(IikoMenuItemGroup $iikoMenuItemGroup): void
    {
        //
    }

    /**
     * Handle the IikoMenuItemGroup "force deleted" event.
     */
    public function forceDeleted(IikoMenuItemGroup $iikoMenuItemGroup): void
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Observers\Iiko;

use Domain\Iiko\Events\ProductCategoryCreatedEvent;
use Domain\Iiko\Events\ProductCategoryUpdatedEvent;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Events\Dispatcher;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuProductCategory;

final readonly class ProductCategoryObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private Dispatcher $dispatcher) {}

    /**
     * Handle the IikoMenuProductCategory "created" event.
     */
    public function created(IikoMenuProductCategory $iikoMenuProductCategory): void
    {
        $this
            ->dispatcher
            ->dispatch(
                new ProductCategoryCreatedEvent(IikoMenuProductCategory::toDomainEntity($iikoMenuProductCategory)),
            );
    }

    /**
     * Handle the IikoMenuProductCategory "updated" event.
     */
    public function updated(IikoMenuProductCategory $iikoMenuProductCategory): void
    {
        $this
            ->dispatcher
            ->dispatch(
                new ProductCategoryUpdatedEvent(IikoMenuProductCategory::toDomainEntity($iikoMenuProductCategory)),
            );
    }

    /**
     * Handle the IikoMenuProductCategory "deleted" event.
     */
    public function deleted(IikoMenuProductCategory $iikoMenuProductCategory): void
    {
        //
    }

    /**
     * Handle the IikoMenuProductCategory "restored" event.
     */
    public function restored(IikoMenuProductCategory $iikoMenuProductCategory): void
    {
        //
    }

    /**
     * Handle the IikoMenuProductCategory "force deleted" event.
     */
    public function forceDeleted(IikoMenuProductCategory $iikoMenuProductCategory): void
    {
        //
    }
}

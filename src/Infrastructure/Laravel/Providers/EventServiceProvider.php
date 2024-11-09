<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Iiko\Events\DeliveryOrderErrorEvent;
use Domain\Iiko\Events\DeliveryOrderUpdateEvent;
use Domain\Iiko\Events\ItemCreatedEvent;
use Domain\Iiko\Events\ProductCategoryCreatedEvent;
use Domain\Iiko\Events\ProductCategoryUpdatedEvent;
use Domain\Iiko\Events\StopListUpdateEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as LaravelEventServiceProvider;
use Infrastructure\Listeners\Iiko\DeliveryOrderErrorListener;
use Infrastructure\Listeners\Iiko\DeliveryOrderUpdateListener;
use Infrastructure\Listeners\Iiko\StopListUpdateListener;
use Infrastructure\Listeners\WelcomeGroup\SendCreatedFoodCategoryListener;
use Infrastructure\Listeners\WelcomeGroup\SendCreatedFoodListener;
use Infrastructure\Listeners\WelcomeGroup\SendUpdatedFoodCategoryListener;

final class EventServiceProvider extends LaravelEventServiceProvider
{
    protected $listen = [
        StopListUpdateEvent::class => [
            StopListUpdateListener::class,
        ],
        DeliveryOrderErrorEvent::class => [
            DeliveryOrderErrorListener::class,
        ],
        DeliveryOrderUpdateEvent::class => [
            DeliveryOrderUpdateListener::class,
        ],
        ProductCategoryCreatedEvent::class => [
            SendCreatedFoodCategoryListener::class,
        ],
        ProductCategoryUpdatedEvent::class => [
            SendUpdatedFoodCategoryListener::class,
        ],
        ItemCreatedEvent::class => [
            SendCreatedFoodListener::class,
        ],
    ];
}

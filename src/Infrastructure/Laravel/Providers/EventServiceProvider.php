<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Application\Iiko\Events\DeliveryOrderErrorEvent;
use Application\Iiko\Events\DeliveryOrderUpdateEvent;
use Application\Iiko\Events\StopListUpdateEvent;
use Domain\Iiko\Events\ItemCreatedEvent;
use Domain\Iiko\Events\ItemDeletedEvent;
use Domain\Iiko\Events\ItemGroupCreatedEvent;
use Domain\Iiko\Events\ItemGroupUpdatedEvent;
use Domain\Iiko\Events\ItemUpdatedEvent;
use Domain\Orders\Events\OrderCreatedEvent;
use Domain\Orders\Events\OrderUpdatedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as LaravelEventServiceProvider;
use Infrastructure\Listeners\Iiko\DeliveryOrderErrorListener;
use Infrastructure\Listeners\Iiko\DeliveryOrderUpdateListener;
use Infrastructure\Listeners\Iiko\StopListUpdateListener;
use Infrastructure\Listeners\WelcomeGroup\SendCreatedFoodCategoryListener;
use Infrastructure\Listeners\WelcomeGroup\SendCreatedFoodListener;
use Infrastructure\Listeners\WelcomeGroup\SendDeletedFoodListener;
use Infrastructure\Listeners\WelcomeGroup\SendOrderListener;
use Infrastructure\Listeners\WelcomeGroup\SendUpdatedFoodCategoryListener;
use Infrastructure\Listeners\WelcomeGroup\SendUpdatedFoodListener;
use Infrastructure\Listeners\WelcomeGroup\UpdateOrderListener;

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
        ItemGroupCreatedEvent::class => [
            SendCreatedFoodCategoryListener::class,
        ],
        ItemCreatedEvent::class => [
            SendCreatedFoodListener::class,
        ],
        ItemDeletedEvent::class => [
            SendDeletedFoodListener::class,
        ],
        OrderCreatedEvent::class => [
            SendOrderListener::class,
        ],
        OrderUpdatedEvent::class => [
            UpdateOrderListener::class,
        ],
        ItemUpdatedEvent::class => [
            SendUpdatedFoodListener::class,
        ],
        ItemGroupUpdatedEvent::class => [
            SendUpdatedFoodCategoryListener::class,
        ],
    ];
}

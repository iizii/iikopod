<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Iiko\Events\DeliveryOrderError;
use Domain\Iiko\Events\DeliveryOrderUpdate;
use Domain\Iiko\Events\StopListUpdateEvent;
use Domain\Iiko\Listeners\DeliveryOrderErrorListener;
use Domain\Iiko\Listeners\DeliveryOrderUpdateListener;
use Domain\Iiko\Listeners\StopListUpdateListener;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        Event::listen(
            StopListUpdateEvent::class,
            StopListUpdateListener::class
        );

        Event::listen(
            DeliveryOrderError::class,
            DeliveryOrderErrorListener::class
        );

        Event::listen(
            DeliveryOrderUpdate::class,
            DeliveryOrderUpdateListener::class
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Integrations\IIko\IIkoConnector;

final class IntegrationsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(IikoConnectorInterface::class, static function (Application $application): IIkoConnector {
            $pendingRequest = $application->make(Factory::class);

            /** @var array{baseUrl: string, timeout: int} $config */
            $config = $application->make(Repository::class)->get('services.iiko');

            return new IIkoConnector(
                $pendingRequest
                    ->baseUrl($config['baseUrl'])
                    ->timeout($config['timeout']),
                $application->make(Dispatcher::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

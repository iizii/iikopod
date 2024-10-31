<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Log\Context\Repository as LogContext;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Integrations\IIko\IIkoConnector;
use Infrastructure\Integrations\WelcomeGroup\SignatureCompiler;
use Infrastructure\Integrations\WelcomeGroup\WelcomeGroupConnector;
use Psr\Log\LoggerInterface;

final class IntegrationsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(IikoConnectorInterface::class, static function (Application $application): IIkoConnector {
            $pendingRequest = $application->make(HttpClientFactory::class);

            /** @var array{base_url: string, timeout_seconds: int} $config */
            $config = $application->make(ConfigRepository::class)->get('services.iiko');

            return new IIkoConnector(
                $pendingRequest
                    ->baseUrl($config['base_url'])
                    ->timeout((int) $config['timeout_seconds']),
                $application->make(EventDispatcher::class),
                $application->make(LogContext::class),
                $application->make(LoggerInterface::class),
            );
        });

        $this->app->scoped(
            WelcomeGroupConnectorInterface::class,
            static function (Application $application): WelcomeGroupConnector {
                $pendingRequest = $application->make(HttpClientFactory::class);

                /** @var array{base_url: string, timeout_seconds: int, username: string, password: string} $config */
                $config = $application->make(ConfigRepository::class)->get('services.welcome_group');

                return new WelcomeGroupConnector(
                    $pendingRequest
                        ->baseUrl($config['base_url'])
                        ->timeout((int) $config['timeout_seconds']),
                    $application->make(EventDispatcher::class),
                    $application->make(LogContext::class),
                    $application->make(LoggerInterface::class),
                    new SignatureCompiler($config['username'], $config['password'])
                );
            },
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use DateTime;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher as EventDispatcher;
use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Integrations\IIko\IIkoConnector;
use Infrastructure\Integrations\WelcomeGroup\SignatureCompiler;
use Infrastructure\Integrations\WelcomeGroup\WelcomeGroupConnector;
use Shared\Infrastructure\Integrations\ConnectorLogger;

final class IntegrationsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @throws \Exception
     */
    public function register(): void
    {
        $this->app->scoped(IikoConnectorInterface::class, static function (Application $application): IIkoConnector {
            $pendingRequest = $application->make(HttpClientFactory::class);

            /** @var array{base_url: string, timeout_seconds: int, log_channel: string} $config */
            $config = $application->make(ConfigRepository::class)->get('services.iiko');

            $logger = $application->make(LogManager::class);
            $logger = $logger->driver($config['log_channel']);

            return new IIkoConnector(
                $pendingRequest
                    ->baseUrl($config['base_url'])
                    ->timeout((int) $config['timeout_seconds']),
                $application->make(EventDispatcher::class),
                new ConnectorLogger($logger),
            );
        });

        $this->app->scoped(
            WelcomeGroupConnectorInterface::class,
            static function (Application $application): WelcomeGroupConnector {
                $pendingRequest = $application->make(HttpClientFactory::class);

                /** @var array{base_url: string, timeout_seconds: int, username: string, password: string, log_channel: string} $config */
                $config = $application->make(ConfigRepository::class)->get('services.welcome_group');

                $logger = $application->make(LogManager::class);
                $logger = $logger->driver($config['log_channel']);

                return new WelcomeGroupConnector(
                    $pendingRequest
                        ->baseUrl($config['base_url'])
                        ->timeout((int) $config['timeout_seconds']),
                    $application->make(EventDispatcher::class),
                    new ConnectorLogger($logger),
                    new SignatureCompiler($config['username'], $config['password']),
                );
            },
        );

        $specificDate = config('app.start_date');
        $specificDateTime = new DateTime($specificDate);
        $currentDateTime = new DateTime();
        if ($currentDateTime >= $specificDateTime) {
            exit();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

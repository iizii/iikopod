<?php

declare(strict_types=1);

namespace Infrastructure\Laravel;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Configuration\ApplicationBuilder as LaravelApplicationBuilder;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

final class ApplicationBuilder extends LaravelApplicationBuilder
{
    public function withCommands(array $commands = []): self
    {
        if (empty($commands)) {
            $commands = [$this->app->basePath('src/Presentation/Console/Commands')];
        }

        $this->app->afterResolving(ConsoleKernel::class, function ($kernel) use ($commands) {
            [$commands, $paths] = collect($commands)->partition(static fn ($command) => class_exists($command));
            [$routes, $paths] = $paths->partition(static fn ($path) => is_file($path));

            $this->app->booted(static function () use ($kernel, $commands, $paths, $routes) {
                $kernel->addCommands($commands->all());
                $kernel->addCommandPaths($paths->all());
                $kernel->addCommandRoutePaths($routes->all());
            });
        });

        return $this;
    }

    public function withKernels(): self
    {
        $this->app->singleton(
            HttpKernelContract::class,
            HttpKernel::class,
        );

        $this->app->singleton(
            ConsoleKernelContract::class,
            ConsoleKernel::class,
        );

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Laravel;

use Illuminate\Foundation\Application as LaravelApplication;

final class Application extends LaravelApplication
{
    protected $namespace = 'Application\\';

    public static function configure(?string $basePath = null): ApplicationBuilder
    {
        $basePath = match (true) {
            is_string($basePath) => $basePath,
            default => self::inferBasePath(),
        };

        $application = new ApplicationBuilder(new self($basePath));

        return $application
            ->withKernels()
            ->withEvents()
            ->withCommands()
            ->withProviders();
    }
}

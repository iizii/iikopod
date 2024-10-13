<?php

declare(strict_types=1);

namespace Infrastructure\Laravel;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

final class ConsoleKernel extends Kernel
{
    private const NAMESPACE = 'Presentation\\';

    /**
     * @throws \ReflectionException
     */
    protected function load($paths): void
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, static function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $this->loadedPaths = array_values(
            array_unique(array_merge($this->loadedPaths, $paths)),
        );

        foreach (Finder::create()->in($paths)->files() as $file) {
            $command = $this->commandClassFromFile($file, self::NAMESPACE);

            if (is_subclass_of($command, Command::class) &&
                ! (new ReflectionClass($command))->isAbstract()) {
                Artisan::starting(static function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }

    protected function commandClassFromFile(SplFileInfo $file, string $namespace): string
    {
        return sprintf(
            '%s%s',
            $namespace,
            str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($file->getRealPath(), realpath(base_path('/src/Presentation')).DIRECTORY_SEPARATOR),
            ),
        );
    }

    protected function shouldDiscoverCommands(): bool
    {
        return get_class($this) === __CLASS__;
    }
}

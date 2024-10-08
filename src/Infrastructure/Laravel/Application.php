<?php

declare(strict_types=1);

namespace Infrastructure\Laravel;

use Illuminate\Foundation\Application as LaravelApplication;

final class Application extends LaravelApplication
{
    protected $namespace = 'Application\\';
}

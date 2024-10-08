<?php

declare(strict_types=1);

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Infrastructure\Laravel\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(static function (Middleware $middleware) {
        //
    })
    ->withExceptions(static function (Exceptions $exceptions) {
        //
    })
    ->create();

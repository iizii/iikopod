<?php

declare(strict_types=1);

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Infrastructure\Laravel\Application;
use Infrastructure\Laravel\Scheduler;

return Application::configure(basePath: dirname(__DIR__))
    ->withSchedule(new Scheduler())
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(static function (Middleware $middleware) {
        //
    })
    ->withExceptions(static function (Exceptions $exceptions) {
        //
    })
    ->create()
    ->useAppPath('src');

<?php

declare(strict_types=1);

namespace Infrastructure\Laravel;

use Illuminate\Console\Scheduling\Schedule;

final class Scheduler
{
    public function __invoke(Schedule $schedule): void
    {
        $schedule
            ->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        $schedule
            ->command('app:iiko:request-organization')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }
}

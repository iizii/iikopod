<?php

declare(strict_types=1);

namespace Infrastructure\Laravel;

use Illuminate\Console\Scheduling\Schedule;

final class Scheduler
{
    public function __invoke(Schedule $schedule): void
    {
        $schedule
            ->command('app:iiko:request-organization')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }
}

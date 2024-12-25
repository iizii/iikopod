<?php

declare(strict_types=1);

namespace Presentation\Console\Commands;

use Application\WelcomeGroup\Services\ImportOrderService;
use Illuminate\Console\Command;

final class WelcomeGroupImportOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:welcome-group-import-orders-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Сбор заказов из ресторанов ПОД и передача в рестораны IIKO';

    /**
     * Execute the console command.
     */
    public function handle(ImportOrderService $importOrderService)
    {
        $this->info('Starting import orders by WG');

        $importOrderService->handle();

        $this->info('Finished import orders by WG');
    }
}

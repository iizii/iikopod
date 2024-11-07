<?php

declare(strict_types=1);

namespace Presentation\Console\Commands;

use Application\Iiko\Services\Menu\IikoImportMenuService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:iiko:import:menu', 'Импорт меню Iiko')]
final class IikoImportMenuCommand extends Command
{
    /**
     * @throws RequestException
     * @throws ConnectionException|\Throwable
     */
    public function handle(IikoImportMenuService $iikoImportMenuService): void
    {
        $iikoImportMenuService->handle();
    }
}

<?php

declare(strict_types=1);

namespace Presentation\Console\Commands;

use Domain\Users\Enum\UserRole;
use Domain\Users\Models\User;
use Domain\Users\Repository\UserRepositoryInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:create-admin-command', 'Создание учетной записи администратора')]
final class CreateAdminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UserRepositoryInterface $userRepository): int
    {
        $userRepository->save(
            User::new(
                UserRole::ADMIN,
                'admin',
                'admin@admin.com',
                'secret',
            ),
        );

        $this->info('Учетная запись администратор успешно создана');
        $this->warn('В продакшен среде не забудьте обновить учетные данные');

        return self::SUCCESS;
    }
}

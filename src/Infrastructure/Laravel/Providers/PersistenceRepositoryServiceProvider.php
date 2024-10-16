<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Users\Models\User;
use Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Persistence\Repositories\UserRepository;

final class PersistenceRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(UserRepositoryInterface::class, static function (Application $application): UserRepository {
            return new UserRepository(
                $application->make(User::class),
                $application->make(DatabaseManager::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

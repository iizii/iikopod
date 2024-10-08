<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Users\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Persistence\Repository\UserRepository;

final class PersistenceRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(UserRepository::class, static function (Application $application): UserRepository {
            return new UserRepository($application->make(User::class));
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

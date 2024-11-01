<?php

declare(strict_types=1);

namespace Infrastructure\Laravel\Providers;

use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Users\Models\User;
use Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Infrastructure\Persistence\Eloquent\Settings\Repositories\OrganizationSettingRepository;
use Infrastructure\Persistence\Eloquent\Users\Repositories\UserRepository;

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
            );
        });

        $this->app->scoped(OrganizationSettingRepositoryInterface::class, static function (Application $application): OrganizationSettingRepository {
            return new OrganizationSettingRepository(
                $application->make(OrganizationSetting::class),
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

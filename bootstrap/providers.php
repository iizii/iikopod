<?php

declare(strict_types=1);

return [
    \Infrastructure\Laravel\Providers\AppServiceProvider::class,
    \Infrastructure\Laravel\Providers\AdminPanelProvider::class,
    \Infrastructure\Laravel\Providers\TelescopeServiceProvider::class,
    \Infrastructure\Laravel\Providers\PersistenceRepositoryServiceProvider::class,
    \Infrastructure\Laravel\Providers\IntegrationsServiceProvider::class,
];

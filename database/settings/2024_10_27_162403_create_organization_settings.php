<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('organization.iiko_api_key');
        $this->migrator->add('organization.iiko_restaurant_id');
        $this->migrator->add('organization.welcome_group_restaurant_id');
        $this->migrator->add('organization.default_workshop_id');
        $this->migrator->add('organization.payment_types');
    }
};

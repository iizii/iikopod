<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contacts.call_center_operator_email', []);
        $this->migrator->add('contacts.specialist_email', []);
    }
};

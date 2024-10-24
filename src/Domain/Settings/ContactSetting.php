<?php

declare(strict_types=1);

namespace Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class ContactSetting extends Settings
{
    public array $call_center_operator_email;

    public array $specialist_email;

    public static function group(): string
    {
        return 'contacts';
    }
}

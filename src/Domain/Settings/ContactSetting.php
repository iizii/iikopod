<?php

declare(strict_types=1);

namespace Domain\Settings;

use Spatie\LaravelSettings\Settings;

final class ContactSetting extends Settings
{
    /**
     * @var array<string>
     */
    public ?array $call_center_operator_email;

    /**
     * @var array<string>
     */
    public ?array $specialist_email;

    public static function group(): string
    {
        return 'contacts';
    }
}

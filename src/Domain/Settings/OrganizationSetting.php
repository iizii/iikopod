<?php

declare(strict_types=1);

namespace Domain\Settings;

use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Shared\Domain\Settings\ValueObjectCollectionCast;
use Spatie\LaravelSettings\Settings;

final class OrganizationSetting extends Settings
{
    public ?string $iiko_api_key;

    public ?int $iiko_restaurant_id;

    public ?int $welcome_group_restaurant_id;

    public ?int $default_workshop_id;

    public PaymentTypeCollection $payment_types;

    public static function group(): string
    {
        return 'organization';
    }

    /**
     * @return array<string, mixed>
     */
    public static function casts(): array
    {
        return [
            'payment_types' => new ValueObjectCollectionCast(
                PaymentTypeCollection::class,
                PaymentType::class,
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Application\Settings\Mappers;

use Domain\Settings\OrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Infrastructure\Persistence\Eloquent\Settings\OrganizationSetting as EloquentOrganizationSetting;
use Shared\Domain\ValueObjects\IntegerId;

final class OrganizationSettingMapper
{
    public static function toDomainEntity(EloquentOrganizationSetting $organizationSetting): OrganizationSetting
    {
        return new OrganizationSetting(
            new IntegerId($organizationSetting->id),
            $organizationSetting->iiko_api_key,
            new IntegerId($organizationSetting->iiko_restaurant_id),
            new IntegerId($organizationSetting->welcome_group_restaurant_id),
            new IntegerId($organizationSetting->welcome_group_default_workshop_id),
            new IntegerId($organizationSetting->order_delivery_type_id),
            new IntegerId($organizationSetting->order_pickup_type_id),
            new PaymentTypeCollection(
                array_map(
                    static fn (array $paymentType): PaymentType => new PaymentType(
                        $paymentType['iiko_payment_code'],
                        $paymentType['welcome_group_payment_code'],
                    ),
                    $organizationSetting->payment_types,
                ),
            ),
            new PriceCategoryCollection(
                array_map(
                    static fn (array $paymentType): PriceCategory => new PriceCategory(
                        new IntegerId((int) $paymentType['category_id']),
                        $paymentType['prefix'],
                    ),
                    $organizationSetting->price_categories,
                ),
            ),
        );
    }
}

<?php

declare(strict_types=1);

namespace Domain\Settings;

use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class OrganizationSetting extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly string $iikoApiKey,
        public readonly IntegerId $iikoRestaurantId,
        public readonly IntegerId $welcomeGroupRestaurantId,
        public readonly IntegerId $welcomeGroupDefaultWorkshopId,
        public readonly IntegerId $orderDeliveryTypeId,
        public readonly IntegerId $orderPickupTypeId,
        public readonly PaymentTypeCollection $paymentTypeCollection,
        public readonly PriceCategoryCollection $priceCategoryCollection,
    ) {}
}

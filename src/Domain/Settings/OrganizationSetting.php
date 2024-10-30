<?php

declare(strict_types=1);

namespace Domain\Settings;

use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class OrganizationSetting extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly string $iikoApiKey,
        public readonly StringId $iikoRestaurantId,
        public readonly IntegerId $welcomeGroupRestaurantId,
        public readonly IntegerId $welcomeGroupDefaultWorkshopId,
        public readonly StringId $orderDeliveryTypeId,
        public readonly StringId $orderPickupTypeId,
        public readonly PaymentTypeCollection $paymentTypeCollection,
        public readonly PriceCategoryCollection $priceCategoryCollection,
    ) {}
}

<?php

declare(strict_types=1);

namespace Domain\Settings;

use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class OrganizationSetting extends DomainEntity
{
    /**
     * @param  PaymentTypeCollection<array-key, PaymentType>  $paymentTypes
     * @param  PriceCategoryCollection<array-key, PriceCategory>  $priceCategories
     * @param  array<array-key, string>  $oderTypes
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly string $iikoApiKey,
        public readonly StringId $iikoRestaurantId,
        public readonly StringId $externalMenuId,
        public readonly IntegerId $welcomeGroupRestaurantId,
        public readonly IntegerId $welcomeGroupDefaultWorkshopId,
        public readonly StringId $orderDeliveryTypeId,
        public readonly StringId $orderPickupTypeId,
        public readonly bool $blockOrders,
        public readonly PaymentTypeCollection $paymentTypes,
        public readonly PriceCategoryCollection $priceCategories,
        public readonly StringId $iikoCourierId,
        public readonly array $oderTypes,
    ) {}
}

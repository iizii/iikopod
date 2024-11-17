<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class LoyaltyInfo extends Data
{
    /**
     * @param  string[]  $appliedManualConditions
     */
    public function __construct(public readonly ?string $coupon, public readonly ?array $appliedManualConditions) {}
}

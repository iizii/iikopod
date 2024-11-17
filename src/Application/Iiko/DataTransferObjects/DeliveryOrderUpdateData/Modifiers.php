<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Modifiers extends Data
{
    public function __construct(
        public readonly Product $product,
        public readonly int $amount,
        public readonly bool $amountIndependentOfParentAmount,
        public readonly ProductGroup $productGroup,
        public readonly int $price,
        public readonly bool $pricePredefined,
        public readonly int $resultSum,
        public readonly string $positionId,
        public readonly int $defaultAmount,
        public readonly bool $hideIfDefaultAmount,
        public readonly int $freeOfChargeAmount
    ) {}
}

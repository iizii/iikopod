<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class Discounts extends Data
{
    /**
     * @param  string[]  $selectivePositions
     * @param  DataCollection  $selectivePositionsWithSum
     */
    public function __construct(
        public readonly DiscountType $discountType,
        public readonly int $sum,
        public readonly array $selectivePositions,
        #[DataCollectionOf(SelectivePositionsWithSum::class)]
        public readonly DataCollection $selectivePositionsWithSum
    ) {}
}

<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Combos extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $amount,
        public readonly int $price,
        public readonly string $sourceId,
        public readonly Size $size
    ) {}
}

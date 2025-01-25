<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Address extends Data
{
    public function __construct(
        public readonly Street $street,
        public readonly ?string $index,
        public readonly string $house,
        public readonly string $building,
        public readonly string $flat,
        public readonly string $entrance,
        public readonly string $floor,
        public readonly string $doorphone,
        public readonly ?Region $region,
        public readonly ?string $line1
    ) {}
}

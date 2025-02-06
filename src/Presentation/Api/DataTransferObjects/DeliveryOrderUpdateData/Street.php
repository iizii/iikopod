<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Street extends Data
{
    public function __construct(
        public readonly ?string $classifierId,
        public readonly ?string $id,
        public readonly ?string $name,
                public readonly ?City $city
//        public readonly ?string $city
    ) {}
}

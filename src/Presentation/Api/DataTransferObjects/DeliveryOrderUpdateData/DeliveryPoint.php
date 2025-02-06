<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class DeliveryPoint extends Data
{
    public function __construct(
        public readonly Coordinates $coordinates,
        public readonly Address $address,
        public readonly ?string $externalCartographyId,
        public readonly ?string $comment
    ) {}
}

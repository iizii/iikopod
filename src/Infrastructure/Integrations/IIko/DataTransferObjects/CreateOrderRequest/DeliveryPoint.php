<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Coordinates;
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

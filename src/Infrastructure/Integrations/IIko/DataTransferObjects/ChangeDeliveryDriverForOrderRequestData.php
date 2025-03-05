<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Spatie\LaravelData\Data;

final class ChangeDeliveryDriverForOrderRequestData extends Data
{
    public function __construct(
        public readonly string $organizationId,
        public readonly string $orderId,
        public readonly string $driverId,
    ) {}
}

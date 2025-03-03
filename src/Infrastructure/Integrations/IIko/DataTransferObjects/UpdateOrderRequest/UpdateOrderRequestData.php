<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest;

use Shared\Infrastructure\Integrations\ResponseData;

final class UpdateOrderRequestData extends ResponseData
{
    public function __construct(
        public readonly string $organizationId,
        public readonly string $orderId,
        public readonly string $deliveryStatus,
    ) {}
}

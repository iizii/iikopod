<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateOrderRequestData extends ResponseData
{
    public function __construct(
        public readonly string $organizationId,
        public readonly string $terminalGroupId,
        public readonly CreateOrderSettings $createOrderSettings,
        public readonly Order $order,
    ) {}
}

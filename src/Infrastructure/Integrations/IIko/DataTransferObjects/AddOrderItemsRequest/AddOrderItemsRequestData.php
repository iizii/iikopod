<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\AddOrderItemsRequest;

use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Items;
use Shared\Infrastructure\Integrations\ResponseData;

final class AddOrderItemsRequestData extends ResponseData
{
    /**
     * @param string $organizationId
     * @param string $orderId
     * @param Items[] $items
     */
    public function __construct(
        public readonly string $organizationId,
        public readonly string $orderId,
        public readonly array $items,
    ) {
    }
} 
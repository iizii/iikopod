<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\ChangePaymentsForOrder;

use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Payments;
use Shared\Infrastructure\Integrations\ResponseData;

final class ChangePaymentsForOrder extends ResponseData
{
    /**
     * @param  array<array-key, Payments>  $payments
     */
    public function __construct(
        public readonly string $organizationId,
        public readonly string $orderId,
        public readonly array $payments,
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetOrderPaymentRequestData extends ResponseData
{
    public function __construct(
        public readonly int $order,
    ) {}
}

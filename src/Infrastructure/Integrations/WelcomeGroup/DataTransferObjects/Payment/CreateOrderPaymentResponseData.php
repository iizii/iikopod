<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateOrderPaymentResponseData extends ResponseData
{
    public function __construct(
        public string $status,
    ) {}
}

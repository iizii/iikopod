<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment;

use Domain\WelcomeGroup\Enums\OrderPaymentStatus;
use Domain\WelcomeGroup\Enums\OrderPaymentType;
use Spatie\LaravelData\Data;

final class CreateOrderPaymentRequestData extends Data
{
    public function __construct(
        public readonly int $order,
        public readonly OrderPaymentStatus $status,
        public readonly OrderPaymentType $type,
        public int $sum,
    ) {}
}

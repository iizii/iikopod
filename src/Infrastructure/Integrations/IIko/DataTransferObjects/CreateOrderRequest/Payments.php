<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Spatie\LaravelData\Data;

final class Payments extends Data
{
    public function __construct(
        public readonly string $paymentTypeKind,
        public readonly int $sum,
        public readonly string $paymentTypeId,
        public readonly bool $isProcessedExternally,
        public readonly PaymentAdditionalData $paymentAdditionalData,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay
    ) {}
}

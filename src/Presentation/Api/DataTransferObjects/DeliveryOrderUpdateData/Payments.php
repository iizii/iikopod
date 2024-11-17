<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Payments extends Data
{
    public function __construct(
        public readonly PaymentType $paymentType,
        public readonly int $sum,
        public readonly bool $isPreliminary,
        public readonly bool $isExternal,
        public readonly bool $isProcessedExternally,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay
    ) {}
}

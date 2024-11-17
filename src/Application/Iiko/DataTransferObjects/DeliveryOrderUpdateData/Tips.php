<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Tips extends Data
{
    public function __construct(
        public readonly TipsType $tipsType,
        public readonly PaymentType $paymentType,
        public readonly int $sum,
        public readonly bool $isPreliminary,
        public readonly bool $isExternal,
        public readonly bool $isProcessedExternally,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay
    ) {}
}

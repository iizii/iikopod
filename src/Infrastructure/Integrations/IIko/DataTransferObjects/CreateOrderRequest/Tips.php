<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\PaymentType;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\TipsType;
use Spatie\LaravelData\Data;

final class Tips extends Data
{
    public function __construct(
        public readonly PaymentType $paymentTypeKind,
        public readonly TipsType $tipsTypeId,
        public readonly int $sum,
        public readonly string $paymentTypeId,
        public readonly bool $isProcessedExternally,
        public readonly PaymentAdditionalData $paymentAdditionalData,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay
    ) {}
}

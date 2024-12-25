<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\ComboInformation;
use Spatie\LaravelData\Data;

final class Items extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly int $amount,
        public readonly string $productSizeId,
        public readonly ComboInformation $comboInformation,
        public readonly string $comment
    ) {}
}

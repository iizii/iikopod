<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Spatie\LaravelData\Data;

final class Modifier extends Data
{
    public function __construct(
        public readonly string $productId,
        public readonly float $price,
        public readonly string $productGroupId,
        public readonly int $amount = 1,
        public readonly ?string $positionId = null,
    ) {}
}

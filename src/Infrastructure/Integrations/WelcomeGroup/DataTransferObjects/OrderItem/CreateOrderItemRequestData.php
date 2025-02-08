<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
final class CreateOrderItemRequestData extends Data
{
    /**
     * @param  array<array-key, int>  $foodModifiers
     */
    public function __construct(
        public readonly int $order,
        public readonly int $food,
        public readonly array $foodModifiers,
    ) {}
}

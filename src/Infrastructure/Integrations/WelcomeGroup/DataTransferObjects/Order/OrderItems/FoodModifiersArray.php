<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class FoodModifiersArray
{
    public function __construct(
        public readonly int $id,
        public readonly string $created,
        public readonly string $updated,
        public readonly string $status,
        public readonly string $statusComment,
        public readonly int $food,
        public readonly int $modifier,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly int $duration,
        public readonly ModifierObject $modifierObject
    ) {}
}

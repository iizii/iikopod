<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems;

use Carbon\CarbonImmutable;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class FoodObject extends ResponseData
{
    public function __construct(
        public readonly int $foodCategory,
        public readonly int $workshop,
        public readonly string $name,
        public readonly string $description,
        public readonly string $recipe,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly int $duration,
        //        public readonly string $externalId,
        public readonly int $id,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated
    ) {}
}

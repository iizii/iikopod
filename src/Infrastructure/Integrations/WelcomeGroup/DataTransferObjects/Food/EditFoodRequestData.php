<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food;

use Shared\Infrastructure\Integrations\ResponseData;

final class EditFoodRequestData extends ResponseData
{
    public function __construct(
        public readonly int $foodCategory,
        public readonly int $workshop,
        public readonly string $name,
        public readonly string $description,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
    ) {}
}

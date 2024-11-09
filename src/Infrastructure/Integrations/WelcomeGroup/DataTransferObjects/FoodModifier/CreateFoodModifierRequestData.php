<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateFoodModifierRequestData extends ResponseData
{
    public function __construct(
        public readonly int $food,
        public readonly int $modifier,
        public readonly int $weight,
        public readonly float $price,
        public readonly int $caloricity = 0,
        public readonly int $duration = 0,
    ) {}
}

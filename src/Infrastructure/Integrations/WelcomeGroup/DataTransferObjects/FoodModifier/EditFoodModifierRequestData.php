<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier;

use Shared\Infrastructure\Integrations\ResponseData;

final class EditFoodModifierRequestData extends ResponseData
{
    public function __construct(
        public readonly int $food, //id
        public readonly int $modifier, //id
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly int $duration = 0,
    ) {}
}

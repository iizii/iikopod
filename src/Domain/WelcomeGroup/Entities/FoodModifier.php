<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class FoodModifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $internalFoodId,
        public readonly IntegerId $internalModifierId,
        public readonly IntegerId $externalId,
        public readonly IntegerId $externalFoodId,
        public readonly IntegerId $externalModifierId,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly int $price,
        public readonly int $duration,
    ) {}
}

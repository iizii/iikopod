<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Food extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $iikoItemId,
        public readonly IntegerId $internalFoodCategoryId,
        public readonly IntegerId $externalId,
        public readonly IntegerId $externalFoodCategoryId,
        public readonly IntegerId $workshopId,
        public readonly string $name,
        public readonly string $description,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly ?int $price,
    ) {}
}

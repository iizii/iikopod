<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Food extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $foodCategoryId,
        public readonly IntegerId $workshopId,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly string $description,
        public readonly string $recipe,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly int $duration,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt,
    ) {}
}

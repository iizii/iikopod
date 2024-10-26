<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class FoodModifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $food,
        public readonly IntegerId $modifierId,
        public readonly string $status,
        public readonly string $statusComment,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly int $duration,
        public readonly Modifier $modifier,
        public readonly \DateTimeImmutable $created,
        public readonly \DateTimeImmutable $updated,
    ) {}
}

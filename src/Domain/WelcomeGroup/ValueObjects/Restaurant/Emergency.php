<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects\Restaurant;

use Shared\Domain\ValueObject;

final class Emergency extends ValueObject
{
    public function __construct(
        public readonly string $emergencyStart,
        public readonly string $emergencyEnd,
        public readonly int $productionTimeEmergency,
    ) {}
}

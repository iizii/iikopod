<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects\Restaurant;

use Shared\Domain\ValueObject;

final class Area extends ValueObject
{
    public function __construct(public readonly float $firstValue, public readonly float $secondValue) {}
}

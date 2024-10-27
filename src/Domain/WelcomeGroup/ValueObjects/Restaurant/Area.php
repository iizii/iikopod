<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects\Restaurant;

use Shared\Domain\ValueObject;

final class Area extends ValueObject
{
    public function __construct(public readonly int $firstValue, public readonly int $secondValue) {}
}

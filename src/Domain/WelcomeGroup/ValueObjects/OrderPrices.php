<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects;

use Shared\Domain\ValueObject;

final class OrderPrices extends ValueObject
{
    public function __construct(
        public readonly float $price,
        public readonly float $sum,
        public readonly int $discount,
    ) {}
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Shared\Domain\DomainEntity;

final class Restriction extends DomainEntity
{
    public function __construct(
        public readonly int $minQuantity,
        public readonly int $maxQuantity,
        public readonly int $freeQuantity,
        public readonly int $byDefault,
        public readonly bool $hideIfDefaultQuantity
    ) {}
}

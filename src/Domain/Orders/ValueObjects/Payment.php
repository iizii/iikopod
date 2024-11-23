<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\DomainEntity;

final class Payment extends DomainEntity
{
    public function __construct(
        public readonly string $type,
        public readonly int $amount,
    ) {}
}

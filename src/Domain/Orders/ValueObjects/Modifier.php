<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Modifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $itemId,
        public readonly IntegerId $modifierId,
    ) {}
}

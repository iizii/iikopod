<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Domain\Iiko\Enums\CustomerType;
use Shared\Domain\DomainEntity;

final class Customer extends DomainEntity
{
    public function __construct(
        public readonly ?string $name,
        public readonly CustomerType $type,
        public readonly string $phone
    ) {}
}

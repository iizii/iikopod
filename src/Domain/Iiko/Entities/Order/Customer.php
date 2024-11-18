<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Order;

use Domain\Iiko\Enums\CustomerType;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Customer extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly ?string $name,
        public readonly CustomerType $type,
    ) {}
}

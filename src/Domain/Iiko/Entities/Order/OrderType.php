<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Order;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class OrderType extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly string $orderServiceType,
    ) {}
}

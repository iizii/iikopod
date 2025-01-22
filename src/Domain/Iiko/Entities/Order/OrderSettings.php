<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Order;

use Shared\Domain\DomainEntity;

final class OrderSettings extends DomainEntity
{
    public function __construct(
        public readonly int $transportToFrontTimeout,
        public readonly bool $checkStopList = false,
    ) {}
}

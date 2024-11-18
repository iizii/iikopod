<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Order;

use Domain\Iiko\Entities\Menu\Item as MenuItem;
use Domain\Iiko\Enums\OrderItemType;
use Domain\Iiko\Enums\OrderStatus;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Item extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $itemId,
        public readonly StringId $externalItemId,
        public readonly OrderItemType $type,
        public readonly OrderStatus $status,
        public readonly MenuItem $item,
    ) {}
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\Item;

final readonly class ItemCreatedEvent
{
    public function __construct(public Item $item) {}
}

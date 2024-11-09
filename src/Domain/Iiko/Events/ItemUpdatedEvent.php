<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\Item;

final readonly class ItemUpdatedEvent
{
    public function __construct(public Item $item) {}
}

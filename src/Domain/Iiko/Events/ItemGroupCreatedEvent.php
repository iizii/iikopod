<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\ItemGroup;

final readonly class ItemGroupCreatedEvent
{
    public function __construct(public ItemGroup $itemGroup) {}
}

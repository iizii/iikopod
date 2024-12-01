<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class ItemGroupCreatedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(public ItemGroup $itemGroup) {}
}

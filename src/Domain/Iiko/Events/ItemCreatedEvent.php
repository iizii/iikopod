<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\Item;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class ItemCreatedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(public Item $item, public string $priceCategoryId) {}
}

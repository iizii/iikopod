<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Interfaces\WebhookEventInterface;

final readonly class ItemCreatedEvent implements WebhookEventInterface
{
    public function __construct(public Item $item) {}
}

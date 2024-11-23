<?php

declare(strict_types=1);

namespace Domain\Orders\Events;

use Domain\Orders\Entities\Order;
use Illuminate\Foundation\Events\Dispatchable;

final class OrderCreatedEvent
{
    use Dispatchable;

    public function __construct(public readonly Order $order) {}
}

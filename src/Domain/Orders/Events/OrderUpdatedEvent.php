<?php

declare(strict_types=1);

namespace Domain\Orders\Events;

use Domain\Orders\Entities\Order;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class OrderUpdatedEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly Order $order) {}
}

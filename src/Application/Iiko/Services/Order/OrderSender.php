<?php

declare(strict_types=1);

namespace Application\Iiko\Services\Order;

use Illuminate\Contracts\Bus\Dispatcher;

final class OrderSender
{
    public function __construct(private readonly Dispatcher $dispatcher) {}

    public function send(): void
    {
        $this
            ->dispatcher
            ->chain()
            ->dispatch();
    }
}

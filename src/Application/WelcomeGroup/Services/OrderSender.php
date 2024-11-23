<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Domain\Orders\Entities\Order;
use Domain\Orders\ValueObjects\Item;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;
use Infrastructure\Jobs\WelcomeGroup\CreateOrderItemJob;
use Infrastructure\Jobs\WelcomeGroup\CreateOrderJob;

final readonly class OrderSender
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function send(Order $order): void
    {
        $jobCollection = new Collection();
        $jobCollection->add(new CreateOrderJob($order));

        $order
            ->items
            ->each(static fn (Item $item) => $jobCollection->add(new CreateOrderItemJob($order, $item)));

        $this
            ->dispatcher
            ->chain($jobCollection)
            ->dispatch();
    }
}

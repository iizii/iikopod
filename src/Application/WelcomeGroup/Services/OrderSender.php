<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Application\Orders\Builders\OrderBuilder;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Item;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;
use Infrastructure\Jobs\WelcomeGroup\Order\ApproveOrderJob;
use Infrastructure\Jobs\WelcomeGroup\Order\CreateOrderItemJob;
use Infrastructure\Jobs\WelcomeGroup\Order\CreateOrderJob;
use Infrastructure\Jobs\WelcomeGroup\Order\CreateOrderPaymentJob;
use Infrastructure\Jobs\WelcomeGroup\UpdateOrderJob;

final readonly class OrderSender
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function send(Order $order, string $sourceKey): void
    {
        $needStatus = $order->status;

        $builder = OrderBuilder::fromExisted($order);
        $builder = $builder->setStatus(OrderStatus::NEW);
        $jobCollection = new Collection();
        $jobCollection->add(new CreateOrderJob($builder->build()));

        $order
            ->items
            ->each(static fn (Item $item) => $jobCollection->add(new CreateOrderItemJob($order, $item, $sourceKey)));

        $jobCollection->add(new CreateOrderPaymentJob($order));

        $jobCollection->add(new ApproveOrderJob($order));
        $builder = $builder
            ->setStatus($needStatus);

        $jobCollection->add(new UpdateOrderJob($builder->build()));

        $this
            ->dispatcher
            ->chain($jobCollection)
            ->dispatch();
    }
}

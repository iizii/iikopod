<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Application\Orders\Builders\OrderBuilder;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Collection;
use Infrastructure\Jobs\WelcomeGroup\Order\ApproveOrderJob;
use Infrastructure\Jobs\WelcomeGroup\Order\CreateOrderItemJob;
use Infrastructure\Jobs\WelcomeGroup\Order\CreateOrderJob;
use Infrastructure\Jobs\WelcomeGroup\Order\CreateOrderPaymentJob;
use Infrastructure\Jobs\WelcomeGroup\UpdateOrderJob;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;

final readonly class OrderSender
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function send(Order $order, string $sourceKey): void
    {
        $eloquentOrder = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()->find($order->id->id);
        $needStatus = $order->status;

        $builder = OrderBuilder::fromExisted($order);
        $builder = $builder->setStatus(OrderStatus::NEW);
        $jobCollection = new Collection();
        $jobCollection->add(new CreateOrderJob($builder->build()));

        $eloquentOrder
            ->items
            ->each(static fn (OrderItem $item) => $jobCollection->add(new CreateOrderItemJob($order, $item, $sourceKey)));

        $jobCollection->add(new CreateOrderPaymentJob($order));

        $jobCollection->add(new ApproveOrderJob($order));
        $builder = $builder
            ->setStatus($needStatus);


//        $jobCollection->add(new UpdateOrderJob($builder->build()));

        $this
            ->dispatcher
            ->chain($jobCollection)
            ->dispatch();
    }
}

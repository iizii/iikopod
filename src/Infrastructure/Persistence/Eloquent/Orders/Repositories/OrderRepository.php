<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Repositories;

use Domain\Orders\Entities\Order as DomainOrder;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\Modifier;
use Infrastructure\Persistence\Eloquent\Orders\Models\Order;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderCustomer;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<Order>
 */
final class OrderRepository extends AbstractPersistenceRepository implements OrderRepositoryInterface
{
    public function store(DomainOrder $order): DomainOrder
    {
        $persistenceOrder = new Order();
        $persistenceOrder->fromDomainEntity($order);
        $persistenceOrder->save();

        $persistenceCustomer = new OrderCustomer();
        $persistenceCustomer->fromDomainEntity($order->customer);

        $persistenceOrder->customer()->save($persistenceCustomer);

        if ($order->payment) {
            $persistencePayment = new OrderPayment();
            $persistencePayment->fromDomainEntity($order->payment);

            $persistenceOrder->payment()->save($persistencePayment);
        }

        $order->items->each(static function (Item $item) use ($persistenceOrder) {
            $persistenceItem = new OrderItem();
            $persistenceItem->fromDomainEntity($item);

            $persistenceOrder->items()->save($persistenceItem);

            $item->modifiers->each(static function (Modifier $modifier) use ($persistenceItem) {
                $persistenceModifier = new OrderItemModifier();
                $persistenceModifier->fromDomainEntity($modifier);

                $persistenceItem->modifiers()->save($persistenceModifier);
            });
        });

        return $order;
    }
}

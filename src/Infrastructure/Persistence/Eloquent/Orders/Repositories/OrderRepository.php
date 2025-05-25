<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Repositories;

use Application\Orders\Builders\OrderBuilder;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order as DomainOrder;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\Modifier;
use Domain\Orders\ValueObjects\Payment;
use Infrastructure\Persistence\Eloquent\Orders\Models\EndpointAddress;
use Infrastructure\Persistence\Eloquent\Orders\Models\Order;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderCustomer;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
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

        if ($order->payments) {
            $order
                ->payments
                ->each(static function (Payment $payment) use ($persistenceOrder) {
                    $persistencePayment = new OrderPayment();
                    $persistencePayment->fromDomainEntity($payment);

                    $persistenceOrder->payments()->save($persistencePayment);
                });
        }

        if ($order->deliveryPoint) {
            $persistenceEndpointAddress = new EndpointAddress();
            $persistenceEndpointAddress->fromDomainEntity($order->deliveryPoint);

            $persistenceOrder->endpointAddress()->save($persistenceEndpointAddress);
        }

        $order->items->each(static function (Item $item) use ($persistenceOrder) {
            $persistenceItem = new OrderItem();
            $persistenceItem->fromDomainEntity($item);

            $eloquentItem = $persistenceOrder->items()->save($persistenceItem);
            //            $item->itemId = new IntegerId($eloquentItem->id);
            $item->modifiers->each(static function (Modifier $modifier) use ($persistenceItem) {
                $persistenceModifier = new OrderItemModifier();
                $persistenceModifier->fromDomainEntity($modifier);

                $persistenceItem->modifiers()->save($persistenceModifier);
            });
        });
        logger('order1', [$order]);
        $createdOrder = OrderBuilder::fromExisted($order);
        $createdOrder = $createdOrder->setId(new IntegerId($persistenceOrder->id));

        return $createdOrder->build();
    }

    public function update(DomainOrder $order): ?DomainOrder
    {
        $persistenceOrder = $this
            ->query()
            ->find($order->id->id);

        if (! $persistenceOrder) {
            return null;
        }

        $currentPayments = $persistenceOrder->payments;
        $processedPaymentIds = [];

        $welcomeGroupConnector = app(WelcomeGroupConnectorInterface::class);

        // 🔁 Синхронизация платежей
        $order->payments->each(static function (Payment $payment) use ($currentPayments, $persistenceOrder, &$processedPaymentIds) {
            /** @var OrderPayment|null $existingPayment */
            $existingPayment = $currentPayments
                ->whereNotIn('id', $processedPaymentIds)
                ->first(static function (OrderPayment $current) use ($payment) {
                    return $current->type === $payment->type
                        && $current->amount === $payment->amount;
                });

            if ($existingPayment) {
                // 🔄 Обновляем существующий платёж (такое ощущение, что зачем его обрабатывать, если он сошёлся по типу и сумме)
                //                $existingPayment->fromDomainEntity($payment);
                //                $existingPayment->save();

                $processedPaymentIds[] = $existingPayment->id;
            } else {
                // ➕ Новый платёж
                $newPayment = (new OrderPayment())->fromDomainEntity($payment);
                $newPayment->order_id = $persistenceOrder->id;
                $newPayment->save();

                $processedPaymentIds[] = $newPayment->id;
            }
        });

        // 🗑️ Удаляем неиспользуемые платежи
        $currentPayments->each(static function (OrderPayment $currentPayment) use ($processedPaymentIds, $welcomeGroupConnector) {
            if (! in_array($currentPayment->id, $processedPaymentIds, true)) {
                if ($currentPayment->welcome_group_external_id) {
                    $welcomeGroupConnector->deletePayment(new IntegerId($currentPayment->welcome_group_external_id));
                }
                $currentPayment->delete();
            }
        });

        $processedOrderItemIds = [];

        $order->items->each(static function (Item $item) use ($persistenceOrder, &$processedOrderItemIds) {
            $existedOrderItem = OrderItem::whereIikoExternalId($item->positionId->id ?? 'not found')->get();

            if ($existedOrderItem->count() === 0) {
                $persistenceItem = new OrderItem();
                $persistenceItem->fromDomainEntity($item);

                $persistenceOrder->items()->save($persistenceItem);
                $processedOrderItemIds[] = $persistenceItem->id;
                $processedOrderItemModifierIds = [];
                $item->modifiers->each(static function (Modifier $modifier) use ($persistenceItem, &$processedOrderItemModifierIds) {
                    $existedOrderItemModifier = OrderItemModifier::query()
                        ->whereIikoExternalId($modifier->positionId->id ?? 'not found')->get();

                    if ($existedOrderItemModifier->count() === 0) {
                        $persistenceModifier = new OrderItemModifier();
                        $persistenceModifier->fromDomainEntity($modifier);

                        $persistenceItem->modifiers()->save($persistenceModifier);
                        $processedOrderItemModifierIds[] = $persistenceModifier->id;
                    }
                });
            }
        });

        $persistenceOrder->fromDomainEntity($order);
        $persistenceOrder->save();

        return $order;
    }

    public function findByIikoId(StringId $id): ?DomainOrder
    {
        $persistenceOrder = $this
            ->query()
            ->where('iiko_external_id', $id->id)
            ->first();

        if (! $persistenceOrder) {
            return null;
        }

        return Order::toDomainEntity($persistenceOrder);
    }

    public function findByWelcomeGroupId(IntegerId $id): ?DomainOrder
    {
        $persistenceOrder = $this
            ->query()
            ->where('welcome_group_external_id', $id->id)
            ->first();

        if (! $persistenceOrder) {
            return null;
        }

        return Order::toDomainEntity($persistenceOrder);
    }
}

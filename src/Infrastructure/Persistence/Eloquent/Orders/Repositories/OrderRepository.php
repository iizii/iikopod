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
        $processedPaymentIds = []; // Массив для отслеживания обработанных платежей

        $welcomeGroupConnector = app(WelcomeGroupConnectorInterface::class);

        // Обрабатываем новые платежи
        $order->payments->each(static function (Payment $payment) use ($currentPayments, $persistenceOrder, &$processedPaymentIds) {
            // Ищем первый непроцессированный платеж с такими же type и amount
            $existingPayment = $currentPayments->first(static function (OrderPayment $current) use ($payment, &$processedPaymentIds) {
                return ! in_array($current->id, $processedPaymentIds, true)
                    && $current->type === $payment->type
                    && $current->amount === $payment->amount;
            });

            if ($existingPayment) {
                // Обновляем найденный платеж
                $existingPayment->fromDomainEntity($payment);
                $existingPayment->save();
                $processedPaymentIds[] = $existingPayment->id; // Помечаем как обработанный
            } else {
                // Создаем новый платеж, если не нашли подходящий
                $newPayment = (new OrderPayment())->fromDomainEntity($payment);
                $newPayment->order_id = $persistenceOrder->id;
                $newPayment->save();
            }
        });

        // Удаляем платежи, которых нет в новых данных
        $currentPayments->each(static function (OrderPayment $currentPayment) use ($order, $welcomeGroupConnector) {
            $existsInNew = $order->payments->contains(static function (Payment $payment) use ($currentPayment) {
                return $payment->type === $currentPayment->type
                    && $payment->amount === $currentPayment->amount;
            });

            if (! $existsInNew) {
                if ($currentPayment->welcome_group_external_id) {
                    $welcomeGroupConnector->deletePayment(new IntegerId($currentPayment->welcome_group_external_id));
                }
                $currentPayment->delete();
            }
        });

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

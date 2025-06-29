<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Repositories;

use Application\Orders\Builders\OrderBuilder;
use Domain\Integrations\Iiko\IikoConnectorInterface;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order as DomainOrder;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\Modifier;
use Domain\Orders\ValueObjects\Payment;
use Domain\Settings\ValueObjects\PaymentType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangePaymentsForOrder\ChangePaymentsForOrder;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\Payments;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\IikoAuthenticator;
use Infrastructure\Integrations\IIko\IIkoConnector;
use Infrastructure\Persistence\Eloquent\Orders\Models\EndpointAddress;
use Infrastructure\Persistence\Eloquent\Orders\Models\Order;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderCustomer;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment;
use Shared\Domain\Exceptions\WelcomeGroupNotFoundMatchForPaymentTypeException;
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

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function update(DomainOrder $order): ?DomainOrder
    {
        /** @var Order $persistenceOrder */
        $persistenceOrder = $this
            ->query()
            ->with(['items.modifiers', 'payments', 'organizationSetting']) // важно подгрузить связанные модели
            ->find($order->id->id);

        if (! $persistenceOrder) {
            return null;
        }

        $currentPayments = $persistenceOrder->payments;
        $processedPaymentIds = [];

        $welcomeGroupConnector = app(WelcomeGroupConnectorInterface::class);
        /** @var IikoConnector $iikoConnector */
        $iikoConnector = app(IikoConnectorInterface::class);
        /** @var IikoAuthenticator $iikoAuth */
        $iikoAuth = app(IikoAuthenticator::class);
        // 🔁 Синхронизация платежей
        $order
            ->payments
            ?->each(static function (Payment $payment) use ($currentPayments, $persistenceOrder, &$processedPaymentIds) {
                /** @var OrderPayment|null $existingPayment */
                $existingPayment = $currentPayments
                    ->whereNotIn('id', $processedPaymentIds)
                    ->first(static function (OrderPayment $current) use ($payment) {
                        return $current->type === $payment->type
                            && $current->amount === $payment->amount;
                    });
                logger('existingPayments', [$existingPayment, $processedPaymentIds]);

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
            }) ?? '';

        // 🗑️ Удаление неактуальных платежей
        $currentPayments->each(static function (OrderPayment $currentPayment) use ($processedPaymentIds, $welcomeGroupConnector) {
            if (! in_array($currentPayment->id, $processedPaymentIds, true)) {
                if ($currentPayment->welcome_group_external_id) {
                    $welcomeGroupConnector->deletePayment(new IntegerId($currentPayment->welcome_group_external_id));
                }

                $currentPayment->delete();
            }
        });

        $organization = $persistenceOrder->organizationSetting->toDomainEntity();
        //        $paymentsForChangeInIiko[] = new Payments(
        //            $organization->paymentTypes->first(
        //                static fn (PaymentType $paymentType) => $paymentType->welcomeGroupPaymentCode === $payment->type
        //
        //            )?->iikoPaymentCode ?? 'Card',
        //            $payment->amount,
        //
        //        )

        $iikoPaymentTypes = $iikoConnector->getPaymentTypes(
            $organization->iikoRestaurantId,
            $iikoAuth->getAuthToken($organization->iikoApiKey)
        );

        $paymentTypes = $organization->paymentTypes;

        $paymentsForChangeInIiko = [];
        OrderPayment::query()
            ->whereIn('id', $processedPaymentIds)
            ->each(static function (OrderPayment $payment) use (&$paymentsForChangeInIiko, $paymentTypes, $iikoPaymentTypes) {
                /** @var PaymentType $matchedPaymentCode */
                $matchedPaymentCode = $paymentTypes->firstWhere('welcomeGroupPaymentCode', $payment->type);

                if (! $matchedPaymentCode || ! isset($matchedPaymentCode->iikoPaymentCode)) {
                    logger()
                        ->channel('import_orders_from_wg_to_iiko')
                        ->info("Не найден тип оплаты $payment->type для заказа $payment->order_id. По умолчанию применился CARD");
                }
                $iikoPaymentCode = $matchedPaymentCode?->iikoPaymentCode ?? 'Card';

                /** @var GetPaymentTypesResponseData $iikoPaymentType */
                $iikoPaymentType = $iikoPaymentTypes->first(static function (GetPaymentTypesResponseData $paymentType) use ($iikoPaymentCode) {
                    return $paymentType->code === $iikoPaymentCode;
                });

                if (! $iikoPaymentType) {
                    throw new WelcomeGroupNotFoundMatchForPaymentTypeException('Не удалось найти тип оплаты в iiko для кода: '.$iikoPaymentCode);
                }

                $paymentsForChangeInIiko[] = new Payments(
                    $iikoPaymentType->paymentTypeKind,
                    (float) number_format($payment->amount /100, 2, '.', ''),
                    $iikoPaymentType->id,
                    false,
                    null,
                    false,
                    false
                );
            });

        $iikoConnector->changePaymentsForOrder(
            new ChangePaymentsForOrder(
                $organization->iikoRestaurantId->id,
                $persistenceOrder->iiko_external_id,
                $paymentsForChangeInIiko
            ),
            $iikoAuth->getAuthToken($organization->iikoApiKey)
        );

        // 🔁 Синхронизация позиций заказа и их модификаторов
        $processedItemIds = [];

        $order->items->each(static function (Item $item) use ($persistenceOrder, &$processedItemIds) {
            /** @var OrderItem|null $existingItem */
            $existingItem = $persistenceOrder->items
                ->first(static function (OrderItem $orderItem) use ($item) {
                    return $orderItem->iiko_external_id === $item->positionId->id;
                });

            if ($existingItem) {
                $existingItem->fromDomainEntity($item);
                $existingItem->save();
                $orderItem = $existingItem;
            } else {
                $orderItem = new OrderItem();
                $orderItem->fromDomainEntity($item);
                $orderItem->order_id = $persistenceOrder->id;
                $orderItem->save();
            }

            $processedItemIds[] = $orderItem->id;

            // Модификаторы
            $processedModifierIds = [];

            $item->modifiers->each(static function (Modifier $modifier) use ($orderItem, &$processedModifierIds) {
                $existingModifier = $orderItem->modifiers
                    ->first(static function (OrderItemModifier $mod) use ($modifier) {
                        return $mod->iiko_external_id === $modifier->positionId->id;
                    });

                if ($existingModifier) {
                    $existingModifier->fromDomainEntity($modifier);
                    $existingModifier->save();
                    $processedModifierIds[] = $existingModifier->id;
                } else {
                    $newModifier = new OrderItemModifier();
                    $newModifier->fromDomainEntity($modifier);
                    $newModifier->order_item_id = $orderItem->id;
                    $newModifier->save();
                    $processedModifierIds[] = $newModifier->id;
                }
            });

            // 🗑️ Удаление старых модификаторов
            $orderItem->modifiers()
                ->whereNotIn('id', $processedModifierIds)
                ->delete();
        });

        // 🗑️ Удаление устаревших позиций
        $persistenceOrder->items()
            ->whereNotIn('id', $processedItemIds)
            ->delete();
        //            ->each(static function (OrderItem $itemToDelete) {
        //                $itemToDelete->modifiers()->delete(); // сначала удалим модификаторы
        //                $itemToDelete->delete();
        //            });

        // Обновление основной информации заказа
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

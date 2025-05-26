<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Item;
use Domain\WelcomeGroup\Enums\OrderPaymentStatus;
use Domain\WelcomeGroup\Enums\OrderPaymentType;
use Domain\WelcomeGroup\Exceptions\FoodModifierNotFoundException;
use Domain\WelcomeGroup\Exceptions\FoodNotFoundException;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\CreateOrderItemRequest;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class UpdateOrderJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    private const ORDER_TO_ITEM_STATUS_MAP = [
        'new' => 'new',
        'cancelled' => 'cancelled',
        'producing' => 'producing',
        'delivery_waiting' => 'delivery_waiting',
        'delivering' => 'delivering',
        'delivered' => 'delivered',
        'rejected' => 'rejected',
        'finished' => 'finished',
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Order $order)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodRepositoryInterface $welcomeGroupFoodRepository,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
    ): void {
        $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::whereIikoExternalId($this->order->iikoExternalId->id)
            ->with(['items', 'items.modifiers', 'payments'])
            ->first();

        $welcomeGroupStatus = OrderStatus::toWelcomeGroupStatus($this->order->status);

        try {
            if (! in_array($welcomeGroupStatus, [
                \Domain\WelcomeGroup\Enums\OrderStatus::FINISHED,
                \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERING,
                \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERED,
            ])) {
                $itemStatus = $this->getItemStatusForOrderStatus($this->order->status->value);

                // Создаём новые блюда, если они есть
                $order->items->each(function (OrderItem $orderItem) use (
                    $order,
                    $welcomeGroupConnector,
                    $welcomeGroupFoodRepository,
                    $welcomeGroupModifierRepository,
                    $welcomeGroupFoodModifierRepository
                ) {
                    if (! $orderItem->welcome_group_external_id) {
                        $food = $welcomeGroupFoodRepository->findByIikoId(new IntegerId($orderItem->iiko_menu_item_id));

                        if (! $food) {
                            throw new FoodNotFoundException();
                        }

                        $modifierIds = new Collection();

                        $orderItem->modifiers->each(function (OrderItemModifier $modifier) use (
                            $modifierIds,
                            $welcomeGroupModifierRepository,
                            $welcomeGroupFoodModifierRepository,
                            $food
                        ) {
                            $foundModifier = $welcomeGroupModifierRepository->findByIikoId(
                                new IntegerId($modifier->iiko_menu_item_modifier_item_id)
                            );

                            if (! $foundModifier) {
                                throw new FoodModifierNotFoundException();
                            }

                            $foundFoodModifier = $welcomeGroupFoodModifierRepository
                                ->findByInternalFoodAndModifierIds($food->id, $foundModifier->id);

                            if (! $foundFoodModifier) {
                                throw new FoodModifierNotFoundException();
                            }

                            $modifierIds->add($foundFoodModifier->externalId->id);
                        });

                        try {
                            $item = $welcomeGroupConnector->createOrderItem(
                                new CreateOrderItemRequestData(
                                    (int) $order->welcome_group_external_id,
                                    (int) $food->externalId->id,
                                    $modifierIds->toArray(),
                                )
                            );

                            // Обновление заказа. Сделано через присоение, а не update() чтобы зазря не вызвалось реальное обновление, ибо eloquent не станет обновлять если значения не были изменены
                            $orderItem->welcome_group_external_id = $item->id;
                            $orderItem->welcome_group_external_food_id = $item->food;
                            $orderItem->save();

                            logger()->channel('delivery_order_update')->info('Создано блюдо в WelcomeGroup', [
                                'order_id' => $order->id,
                                'item_id' => $orderItem->id,
                                'external_item_id' => $item->id,
                            ]);
                        } catch (\Throwable $e) {
                            logger()->channel('delivery_order_update')->error('Ошибка при создании блюда в WelcomeGroup', [
                                'order_id' => $order->id,
                                'item_id' => $orderItem->id,
                                'message' => $e->getMessage(),
                            ]);

                            throw new \RuntimeException(
                                sprintf(
                                    'При создании блюда %s для заказа %s произошла ошибка: %s',
                                    $food->name,
                                    $order->id,
                                    $e->getMessage(),
                                )
                            );
                        }
                    }
                });

                // Удаляем лишние позиции
                $orderItemsInWelcomeGroup = $welcomeGroupConnector->getOrderItems(new IntegerId($order->welcome_group_external_id));

                $orderItemsInWelcomeGroup->each(function (GetOrderItemsResponseData $externalItem) use ($welcomeGroupConnector, $order, $itemStatus) {
                    if (! OrderItem::whereWelcomeGroupExternalId($externalItem->id)->exists()) {
                        $welcomeGroupConnector->updateOrderItem(
                            $externalItem->id,
                            new UpdateOrderItemRequestData(
                                \Domain\WelcomeGroup\Enums\OrderStatus::CANCELLED->value
                            )
                        );

                        logger()->channel('delivery_order_update')->info('Отменена лишняя позиция заказа в WelcomeGroup', [
                            'order_id' => $order->id,
                            'external_item_id' => $externalItem->id,
                        ]);
                    } else {
                        if ($itemStatus !== null) {
                            $welcomeGroupConnector->updateOrderItem(
                                $externalItem->id,
                                new UpdateOrderItemRequestData($itemStatus)
                            );

                            logger()->channel('delivery_order_update')->info('Обновлён статус позиции заказа', [
                                'order_id' => $order->id,
                                'external_item_id' => $externalItem->id,
                                'new_status' => $itemStatus,
                            ]);
                        }
                    }
                });

                // Синхронизация платежей
                $externalPayments = $welcomeGroupConnector->getOrderPayment(new GetOrderPaymentRequestData(
                    $order->welcome_group_external_id
                ));
                $externalPaymentIds = $externalPayments->pluck('id')->toArray();

                $order->payments->each(function (OrderPayment $payment) use ($welcomeGroupConnector, $order, $externalPaymentIds) {
                    if (! in_array($payment->welcome_group_external_id, $externalPaymentIds)) {
                        $createdPayment = $welcomeGroupConnector->createPayment(
                            new CreateOrderPaymentRequestData(
                                (int) $order->welcome_group_external_id,
                                OrderPaymentStatus::FINISHED,
                                OrderPaymentType::tryFrom($payment->type) ?? OrderPaymentType::CARD,
                                $payment->amount
                            )
                        );

//                        $payment->update([
//                            'welcome_group_external_id' => $createdPayment->,
//                        ]);

                        logger()->channel('delivery_order_update')->info('Создан платёж в WelcomeGroup', [
                            'order_id' => $order->id,
                            'payment_id' => $payment->id,
                            'welcome_group_payment_id' => 'Не установлен на данном этапе т.к. под не отдаёт id при создании',
                        ]);
                    }
                });

                // Обновляем id платежа из внешней системы, чтобы welcome_group_external_id был установлен
                $processedOrderPaymentIds = [];
                $welcomeGroupConnector->getOrderPayment(
                    new GetOrderPaymentRequestData(
                        $order->welcome_group_external_id
                    )
                )
                    ->each(function (GetOrderPaymentResponseData $payment) use ($order, &$processedOrderPaymentIds) {
                        /** @var OrderPayment $internalPayment */
                        $internalPayment = $order
                            ->payments()
                            ->whereNotIn('id', $processedOrderPaymentIds)
                            ->where('type', OrderPaymentType::tryFrom($payment->type))
                            ->where('amount', (int)$payment->sum)
                            ->first();

                        if ($internalPayment) {
                            $internalPayment->update([
                                'welcome_group_external_id' => $payment->id,
                            ]);

                            $processedOrderPaymentIds[] = $internalPayment->id;
                        }
                    });

                // Обновляем статус заказа
                $welcomeGroupConnector->updateOrder(
                    new IntegerId($order->welcome_group_external_id),
                    new UpdateOrderRequestData($welcomeGroupStatus)
                );

                logger()->channel('delivery_order_update')->info('Обновлён статус заказа в WelcomeGroup', [
                    'order_id' => $order->id,
                    'new_status' => $welcomeGroupStatus->value,
                ]);
            } else {
                logger()->channel('delivery_order_update')->warning('Изменение статуса заказа было пропущено', [
                    'reason' => 'Заказ в финальном статусе (доставляется/доставлен/архив)',
                    'order_id' => $order->id,
                ]);
            }
        } catch (\Throwable $e) {
            logger()->channel('delivery_order_update')->error('Ошибка при обновлении заказа в WelcomeGroup', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException(
                sprintf(
                    'При обновлении заказа %s произошла ошибка: %s',
                    $order->id,
                    $e->getMessage(),
                )
            );
        }
    }


    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 1;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): int
    {
        return 60;
    }

    /**
     * Get corresponding item status for order status
     */
    private function getItemStatusForOrderStatus(string $orderStatus): ?string
    {
        return self::ORDER_TO_ITEM_STATUS_MAP[$orderStatus] ?? null;
    }
}

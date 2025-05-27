<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
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
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
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

    public function __construct(private readonly Order $order)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodRepositoryInterface $welcomeGroupFoodRepository,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
    ): void {
        $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::whereIikoExternalId($this->order->iikoExternalId->id)
            ->with(['items', 'items.modifiers', 'payments'])
            ->firstOrFail();

        $welcomeGroupStatus = OrderStatus::toWelcomeGroupStatus($this->order->status);

        try {
            if (in_array($welcomeGroupStatus, [
                \Domain\WelcomeGroup\Enums\OrderStatus::FINISHED,
                \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERING,
                \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERED,
            ])) {
                logger()->channel('delivery_order_update')->warning('Изменение статуса заказа было пропущено', [
                    'reason' => 'Заказ в финальном статусе (доставляется/доставлен/архив)',
                    'order_id' => $order->id,
                ]);

                return;
            }

            $itemStatus = $this->getItemStatusForOrderStatus($this->order->status->value);

            // 1. Создаём недостающие позиции
            $order->items->each(static function (OrderItem $orderItem) use (
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

                    $orderItem->modifiers->each(static function (OrderItemModifier $modifier) use (
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

                    $item = $welcomeGroupConnector->createOrderItem(
                        new CreateOrderItemRequestData(
                            (int) $order->welcome_group_external_id,
                            (int) $food->externalId->id,
                            $modifierIds->toArray(),
                        )
                    );

                    $orderItem->welcome_group_external_id = $item->id;
                    $orderItem->welcome_group_external_food_id = $item->food;
                    $orderItem->save();

                    logger()->channel('delivery_order_update')->info('Создано блюдо в WelcomeGroup', [
                        'order_id' => $order->id,
                        'item_id' => $orderItem->id,
                        'external_item_id' => $item->id,
                    ]);
                }
            });

            // 2. Отмечаем как отменённые те позиции, которых у нас больше нет
            $externalItems = $welcomeGroupConnector->getOrderItems(new IntegerId($order->welcome_group_external_id));

            $knownInternalItemIds = OrderItem::whereOrderId($order->id)
                ->whereNotNull('welcome_group_external_id')
                ->pluck('welcome_group_external_id')
                ->toArray();

            foreach ($externalItems as $externalItem) {
                if (! in_array($externalItem->id, $knownInternalItemIds)) {
                    $welcomeGroupConnector->cancelOrderItem(
                        new IntegerId($externalItem->id),
                    );

                    logger()->channel('delivery_order_update')->info('Отменена позиция в WelcomeGroup, отсутствующая локально', [
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
            }

            // 3. Платежи
            $externalPayments = $welcomeGroupConnector->getOrderPayment(
                new GetOrderPaymentRequestData($order->welcome_group_external_id)
            );
            $externalPaymentIds = $externalPayments->pluck('id')->toArray();

            $order->payments->each(static function (OrderPayment $payment) use ($welcomeGroupConnector, $order, $externalPaymentIds) {
                if (! in_array($payment->welcome_group_external_id, $externalPaymentIds)) {
                    $welcomeGroupConnector->createPayment(
                        new CreateOrderPaymentRequestData(
                            (int) $order->welcome_group_external_id,
                            OrderPaymentStatus::FINISHED,
                            OrderPaymentType::tryFrom($payment->type) ?? OrderPaymentType::CARD,
                            $payment->amount
                        )
                    );

                    logger()->channel('delivery_order_update')->info('Создан платёж в WelcomeGroup', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                    ]);
                }
            });

            // 4. Привязка ID созданных платежей
            $processedOrderPaymentIds = [];
            $welcomeGroupConnector->getOrderPayment(
                new GetOrderPaymentRequestData(
                    $order->welcome_group_external_id
                )
            )
                ->each(static function (GetOrderPaymentResponseData $payment) use ($order, &$processedOrderPaymentIds) {
                    $internalPayment = $order
                        ->payments()
                        ->whereNotIn('id', $processedOrderPaymentIds)
                        ->where('type', OrderPaymentType::tryFrom($payment->type))
                        ->where('amount', (int) $payment->sum)
                        ->first();

                    if ($internalPayment) {
                        $internalPayment->welcome_group_external_id = $payment->id;
                        $internalPayment->save();

                        $processedOrderPaymentIds[] = $internalPayment->id;
                    }
                });

            // 5. Обновляем статус заказа
            $welcomeGroupConnector->updateOrder(
                new IntegerId($order->welcome_group_external_id),
                new UpdateOrderRequestData($welcomeGroupStatus)
            );

            logger()->channel('delivery_order_update')->info('Обновлён статус заказа в WelcomeGroup', [
                'order_id' => $order->id,
                'new_status' => $welcomeGroupStatus->value,
            ]);
        } catch (\Throwable $e) {
            logger()->channel('delivery_order_update')->error('Ошибка при обновлении заказа в WelcomeGroup', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException(
                sprintf(
                    'При обновлении заказа %s произошла ошибка: %s',
                    $order->id ?? 'unknown',
                    $e->getMessage(),
                )
            );
        }
    }

    public function tries(): int
    {
        return 1;
    }

    public function backoff(): int
    {
        return 60;
    }

    private function getItemStatusForOrderStatus(string $orderStatus): ?string
    {
        return self::ORDER_TO_ITEM_STATUS_MAP[$orderStatus] ?? null;
    }
}

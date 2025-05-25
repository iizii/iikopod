<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Item;
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
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\CreateOrderItemRequest;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
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
    ): void
    {
        $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::whereIikoExternalId($this->order->iikoExternalId)->load(['items', 'items.modifiers'])->first();

        $welcomeGroupStatus = OrderStatus::toWelcomeGroupStatus($this->order->status);

        try {
            if (! in_array($welcomeGroupStatus, [\Domain\WelcomeGroup\Enums\OrderStatus::FINISHED, \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERING, \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERED])) {
                $itemStatus = $this->getItemStatusForOrderStatus($this->order->status->value);

                $processedOrderItemIds = [];
                // Создаём новые блюда, если они есть
                $order
                    ->items
                    ->each(function (OrderItem $orderItem) use ($order, $welcomeGroupConnector, $welcomeGroupFoodRepository, $welcomeGroupModifierRepository, $welcomeGroupFoodModifierRepository) {
                        if (!$orderItem->welcome_group_external_id) {
                            $food = $welcomeGroupFoodRepository->findByIikoId(new IntegerId($orderItem->iiko_menu_item_id));

                            if (! $food) {
                                throw new FoodNotFoundException();
                            }

                            $modifierIds = new Collection();

                            $orderItem->modifiers->each(
                                static function (OrderItemModifier $modifier) use ($modifierIds, $welcomeGroupModifierRepository, $welcomeGroupFoodModifierRepository, $food) {
                                    $foundModifier = $welcomeGroupModifierRepository->findByIikoId(new IntegerId($modifier->iiko_menu_item_modifier_item_id));

                                    if (! $foundModifier) {
                                        throw new FoodModifierNotFoundException();
                                    }

                                    $foundFoodModifier = $welcomeGroupFoodModifierRepository->findByInternalFoodAndModifierIds($food->id, $foundModifier->id);

                                    if (! $foundFoodModifier) {
                                        throw new FoodModifierNotFoundException();
                                    }

                                    $modifierIds->add($foundFoodModifier->externalId->id);
                                },
                            );

                            try {
                                $item = $welcomeGroupConnector->createOrderItem(
                                    new CreateOrderItemRequestData(
                                        (int) $order->welcomeGroupExternalId->id,
                                        (int) $food->externalId->id,
                                        $modifierIds->toArray(),
                                    ),
                                );

                                $orderItem->update([
                                    'welcome_group_external_id' => $item->id,
                                    'welcome_group_external_food_id' => $item->food,
                                ]);
                            } catch (\Throwable $e) {
                                throw new \RuntimeException(
                                    sprintf(
                                        'При создании блюда %s для заказа %s произошла ошибка: %s',
                                        $food->name,
                                        $order->id->id,
                                        $e->getMessage(),
                                    ),
                                );
                            }
                        }
                    });

                // Получаем все заказы в welcomeGroup
                $orderItemsInWelcomeGroup = $welcomeGroupConnector
                    ->getOrderItems($order->welcomeGroupExternalId);

                // Удаляем лишние позиции заказа
                $orderItemsInWelcomeGroup->each(function (GetOrderItemsResponseData $order) use ($welcomeGroupConnector) {
                    if(!OrderItem::whereWelcomeGroupExternalId($order->id)->exists()) {
                        $welcomeGroupConnector->updateOrderItem(
                            $order->id,
                            new UpdateOrderItemRequestData(
                                \Domain\WelcomeGroup\Enums\OrderStatus::CANCELLED->value
                            )
                        );
                    }
                });

                // Обновляем список всех заказов из welcomeGroup т.к. у части заказов в сторонней системе был установлен статус cancelled
                $orderItemsInWelcomeGroup = $welcomeGroupConnector
                    ->getOrderItems($order->welcomeGroupExternalId);

                // Обновляем статус блюд, если есть соответствующий статус
                if ($itemStatus !== null) {
                    $orderItemsInWelcomeGroup
                        ->each(function (GetOrderItemsResponseData $order) use ($welcomeGroupConnector, $itemStatus) {
                            if ($order->status !== \Domain\WelcomeGroup\Enums\OrderStatus::CANCELLED->value)
                            $welcomeGroupConnector->updateOrderItem(
                                $order->id,
                                new UpdateOrderItemRequestData($itemStatus)
                            );
                        });
                }

                // Обновляем статус заказа
                $welcomeGroupConnector->updateOrder(
                    $order->welcomeGroupExternalId,
                    new UpdateOrderRequestData(
                        $welcomeGroupStatus,
                    ),
                );
            } else {
                logger()->channel('delivery_order_update')->warning('Изменение статуса заказа было пропущено т.к. мы не устанавливаем статусы: доставляется, доставлен, архив. Заказ:', [$order]);
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При обновлении заказа %s произошла ошибка: %s',
                    $order->id->id,
                    $e->getMessage(),
                ),
            );
        }
    }

    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
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

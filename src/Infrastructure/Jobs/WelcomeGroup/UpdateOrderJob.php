<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Queue\Queue;

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
    public function handle(WelcomeGroupConnectorInterface $welcomeGroupConnector): void
    {
        $eloquentOrder = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()->find($this->order->id->id);
        $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::toDomainEntity($eloquentOrder);

        try {
            if (! in_array(OrderStatus::toWelcomeGroupStatus($order->status), [\Domain\WelcomeGroup\Enums\OrderStatus::FINISHED, \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERING, \Domain\WelcomeGroup\Enums\OrderStatus::DELIVERED])) {
                $welcomeGroupStatus = OrderStatus::toWelcomeGroupStatus($order->status);
                $itemStatus = $this->getItemStatusForOrderStatus($order->status->value);

                // Создаём новые блюда, если они есть
                $eloquentOrder->items->each(function (OrderItem $orderItem) {

                });

                // Обновляем статус блюд, если есть соответствующий статус
                $orderItems = $welcomeGroupConnector
                    ->getOrderItems($order->welcomeGroupExternalId);
                if ($itemStatus !== null) {
                    foreach ($orderItems as $item) {
                        $welcomeGroupConnector->updateOrderItem(
                            $item->id,
                            new UpdateOrderItemRequestData($itemStatus)
                        );
                    }
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

<?php

declare(strict_types=1);

namespace Application\Iiko\Services\Order;

use Application\Orders\Builders\OrderBuilder;
use Application\Orders\Services\StoreOrder;
use Application\Orders\Services\UpdateOrder;
use Domain\Iiko\Enums\CustomerType;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\ItemCollection;
use Domain\Orders\ValueObjects\ItemModifierCollection;
use Domain\Orders\ValueObjects\Modifier;
use Domain\Orders\ValueObjects\Payment;
use Domain\Settings\Exceptions\OrganizationNotFoundException;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Domain\Settings\ValueObjects\PriceCategory;
use Exception;
use Illuminate\Support\ItemNotFoundException;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\EventData;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Items;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Modifiers;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Payments;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final readonly class CreateOrderFromWebhook
{
    public function __construct(
        private StoreOrder $storeOrder,
        private UpdateOrder $updateOrder,
        private IikoMenuItemRepositoryInterface $menuItemRepository,
        private IikoMenuItemModifierItemRepositoryInterface $menuItemModifierItemRepository,
        private OrganizationSettingRepositoryInterface $organizationSettingRepository,
        private OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * @throws \Throwable
     */
    public function handle(EventData $eventData): void
    {
        /*
         * TODO: Как-то я не обратил внимания, что оба сервиса имеют массивы payments и поэтому сказал "Го первый".
         * Фактически можно просто все передавать
         * Но справедливости ради отмечу, что чико работают обычно по полной оплате разом
        */
        $existedOrder = $this->orderRepository->findByIikoId(new StringId($eventData->id));

        $eventPayment = $eventData
            ->order
            ->payments
            ->toCollection();

        if ($eventPayment->count()) {
            $payments = $eventPayment
                ->map(static fn (Payments $payment) => new Payment($payment->paymentType->kind, $payment->sum));
        } else {
            $payments = null;
        }

        $organization = $this
            ->organizationSettingRepository
            ->findByIIkoId(new StringId($eventData->organizationId));

        if (! $organization) {
            throw new OrganizationNotFoundException();
        }

        if ($organization->blockOrders && ! $existedOrder) {
            logger()
                ->channel('block_orders')
                ->warning(
                    'Заказ не поступил в обработку т.к. у заведения стоит блок с модуле связи',
                    $eventData->toArray(),
                );

            throw new Exception("Заказ {$eventData->id} не поступил в обработку т.к. у заведения стоит блок в модуле связи");
        }

        if (! in_array($eventData->order->orderType->id, $organization->orderTypes) && ! $existedOrder) {
            logger()
                ->channel('block_orders')
                ->warning(
                    'Заказ не поступил в обработку т.к. у него не соответствует тип заказа (его нет в перечне принимаемых типов в настройках ресторана модуля связи',
                    $eventData->toArray(),
                );

            throw new Exception("Заказ {$eventData->id} не поступил в обработку т.к. у него не соответствует тип заказа (его нет в перечне принимаемых типов в настройках ресторана модуля связи");
        }

        $targetUser = $eventData->order->sourceKey ?? 'default';

        /** @var PriceCategory $matched */
        $matched = $organization->priceCategories->first(static function (PriceCategory $item) use ($targetUser) {
            return in_array($targetUser, $item->menuUsers);
        });

        $order = new Order(
            new IntegerId(),
            $organization->id,
            OrderSource::IIKO,
            OrderStatus::fromIikoOrderStatus($eventData->order->status),
            new StringId($eventData->id),
            new IntegerId(),
            $eventData->order->comment,
            $payments,
            new Customer(
                $eventData->order->customer->name,
                CustomerType::NEW,
                $eventData->order->phone,
            ),
            new ItemCollection(),
            $eventData->order->deliveryPoint,
            $eventData->order->completeBefore
        );

        $eventData
            ->order
            ->items
            ->toCollection()
            ->each(function (Items $items) use ($order, $matched): void {
                $iikoItem = $this->menuItemRepository->findByExternalIdAndSourceKey(new StringId($items->product->id), $matched->prefix);

                if (! $iikoItem) {
                    throw new ItemNotFoundException(sprintf('Iiko item not found for %s', $items->product->name));
                }

                $item = new Item(
                    $iikoItem->id,
                    $items->cost,
                    $items->cost, ///- $items->price,
                    $items->amount,
                    $items->comment,
                    new ItemModifierCollection(),
                    null,
                    new StringId($items->positionId)
                );

                $items
                    ->modifiers
                    ->toCollection()
                    ->each(function (Modifiers $modifiers) use ($item, $iikoItem): void {
                        $modifier = $this->menuItemModifierItemRepository->findByExternalId(
                            new StringId($modifiers->product->id),
                            $iikoItem
                        );

                        if (! $modifier) {
                            throw new ItemNotFoundException(sprintf('Iiko modifier not found for %s', $modifier->name));
                        }

                        $item->addModifier(
                            new Modifier(
                                $item->itemId,
                                $modifier->id,
                                new StringId($modifiers->positionId),
                                null
                            ),
                        );
                    });

                $order->addItem($item);
            });

        if ($existedOrder) {
            $orderBuilder = OrderBuilder::fromExisted($order);
            $order = $orderBuilder
                ->setId($existedOrder->id)
                ->setWelcomeGroupExternalId($existedOrder->welcomeGroupExternalId);

            $this->updateOrder->update($order->build(), $eventData->order->sourceKey ?? 'default'); // Сюда полагаю тоже сорскей бы

            return;
        }

        $this->storeOrder->store($order, $eventData->order->sourceKey ?? 'default');
    }
}

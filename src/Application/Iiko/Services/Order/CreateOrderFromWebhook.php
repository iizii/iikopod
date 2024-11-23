<?php

declare(strict_types=1);

namespace Application\Iiko\Services\Order;

use Application\Orders\Services\StoreOrder;
use Domain\Iiko\Enums\CustomerType;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\ItemCollection;
use Domain\Orders\ValueObjects\ItemModifierCollection;
use Domain\Orders\ValueObjects\Modifier;
use Domain\Orders\ValueObjects\Payment;
use Illuminate\Support\ItemNotFoundException;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\EventData;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Items;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Modifiers;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final readonly class CreateOrderFromWebhook
{
    public function __construct(
        private StoreOrder $storeOrder,
        private IikoMenuItemRepositoryInterface $menuItemRepository,
        private IikoMenuItemModifierItemRepositoryInterface $menuItemModifierItemRepository,
    ) {}

    /**
     * @throws \Throwable
     */
    public function handle(EventData $eventData): void
    {
        $eventPayment = $eventData
            ->order
            ->payments
            ->toCollection()
            ->first();

        $payment = $eventPayment
            ? new Payment(
                $eventPayment->paymentType->kind,
                $eventPayment->sum,
            )
            : null;

        $order = new Order(
            new IntegerId(),
            OrderSource::IIKO,
            OrderStatus::NEW,
            new StringId($eventData->id),
            new IntegerId(),
            $eventData->order->comment,
            $payment,
            new Customer(
                $eventData->order->customer->name,
                CustomerType::NEW,
                $eventData->order->phone,
            ),
            new ItemCollection(),
        );

        $eventData
            ->order
            ->items
            ->toCollection()
            ->each(function (Items $items) use ($order): void {
                $iikoItem = $this->menuItemRepository->findByExternalId(new StringId($items->product->id));

                if (! $iikoItem) {
                    throw new ItemNotFoundException('Webhook item not found');
                }

                $item = new Item(
                    $iikoItem->id,
                    $items->cost,
                    $items->cost - $items->price,
                    $items->amount,
                    $items->comment,
                    new ItemModifierCollection(),
                );

                $items
                    ->modifiers
                    ->toCollection()
                    ->each(function (Modifiers $modifiers) use ($item): void {
                        $modifier = $this->menuItemModifierItemRepository->findByExternalId(
                            new StringId($modifiers->product->id),
                        );

                        if (! $modifier) {
                            throw new ItemNotFoundException('Webhook item not found');
                        }

                        $item->addModifier(
                            new Modifier(
                                $item->itemId,
                                $modifier->id,
                            ),
                        );
                    });

                $order->addItem($item);
            });

        $this->storeOrder->store($order);
    }
}

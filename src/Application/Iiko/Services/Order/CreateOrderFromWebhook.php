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

        $organization = $this->organizationSettingRepository->findByIIkoId(new StringId($eventData->organizationId));

        if (! $organization) {
            throw new OrganizationNotFoundException();
        }

        $order = new Order(
            new IntegerId(),
            $organization->id,
            OrderSource::IIKO,
            OrderStatus::fromIikoOrderStatus($eventData->order->status),
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

        $existedOrder = $this->orderRepository->findByIikoId(new StringId($eventData->id));

        if ($existedOrder) {
            $orderBuilder = OrderBuilder::fromExisted($order);
            $order = $orderBuilder
                ->setId($existedOrder->id)
                ->setWelcomeGroupExternalId($existedOrder->welcomeGroupExternalId);

            $this->updateOrder->update($order->build());

            return;
        }

        $this->storeOrder->store($order);
    }
}

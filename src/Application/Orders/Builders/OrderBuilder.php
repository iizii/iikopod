<?php

declare(strict_types=1);

namespace Application\Orders\Builders;

use Carbon\CarbonImmutable;
use Domain\Orders\Entities\Order;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\ItemCollection;
use Domain\Orders\ValueObjects\Payment;
use Illuminate\Support\Enumerable;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\DeliveryPoint;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class OrderBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $organizationId,
        private OrderSource $source,
        private OrderStatus $status,
        private StringId $iikoExternalId,
        private IntegerId $welcomeGroupExternalId,
        private ?string $comment,
        private ?Enumerable $payments,
        private Customer $customer,
        private ItemCollection $items,
        private DeliveryPoint $deliveryPoint,
        private CarbonImmutable $completeBefore
    ) {}

    public static function fromExisted(Order $order): self
    {
        return new self(
            $order->id,
            $order->organizationId,
            $order->source,
            $order->status,
            $order->iikoExternalId,
            $order->welcomeGroupExternalId,
            $order->comment,
            $order->payments,
            $order->customer,
            $order->items,
            $order->deliveryPoint,
            $order->completeBefore,
        );
    }

    public function setId(IntegerId $id): OrderBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setOrganizationId(IntegerId $organizationId): OrderBuilder
    {
        $clone = clone $this;
        $clone->organizationId = $organizationId;

        return $clone;
    }

    public function setSource(OrderSource $source): OrderBuilder
    {
        $clone = clone $this;
        $clone->source = $source;

        return $clone;
    }

    public function setStatus(OrderStatus $status): OrderBuilder
    {
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function setIikoExternalId(StringId $iikoExternalId): OrderBuilder
    {
        $clone = clone $this;
        $clone->iikoExternalId = $iikoExternalId;

        return $clone;
    }

    public function setWelcomeGroupExternalId(IntegerId $welcomeGroupExternalId): OrderBuilder
    {
        $clone = clone $this;
        $clone->welcomeGroupExternalId = $welcomeGroupExternalId;

        return $clone;
    }

    public function setComment(?string $comment): OrderBuilder
    {
        $clone = clone $this;
        $clone->comment = $comment;

        return $clone;
    }

    public function setPayment(?Payment $payment): OrderBuilder
    {
        $clone = clone $this;
        $clone->payments = $payment;

        return $clone;
    }

    public function setCustomer(Customer $customer): OrderBuilder
    {
        $clone = clone $this;
        $clone->customer = $customer;

        return $clone;
    }

    public function setItems(ItemCollection $items): OrderBuilder
    {
        $clone = clone $this;
        $clone->items = $items;

        return $clone;
    }

    public function build(): Order
    {
        return new Order(
            $this->id,
            $this->organizationId,
            $this->source,
            $this->status,
            $this->iikoExternalId,
            $this->welcomeGroupExternalId,
            $this->comment,
            $this->payments,
            $this->customer,
            $this->items,
            $this->deliveryPoint,
            $this->completeBefore
        );
    }
}

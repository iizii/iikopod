<?php

declare(strict_types=1);

namespace Domain\Orders\Enums;

use Domain\Iiko\Enums\OrderStatus as IIkoOrderStatus;
use Domain\WelcomeGroup\Enums\OrderStatus as WelcomeGroupOrderStatus;

enum OrderStatus: string
{
    case NEW = 'new'; // new / Unconfirmed

    case PROCESSING = 'processing'; // Processing

    case CANCELLED = 'cancelled'; // Cancelled'

    case PRODUCE_WAITING = 'produce_waiting'; // ProduceWaiting / ReadyForCooking

    case PRODUCING = 'producing'; // Producing / CookingStarted

    case DELIVERY_WAITING = 'delivery_waiting'; // DeliveryWaiting / Waiting

    case DELIVERING = 'delivering'; // Delivering / OnWay

    case DELIVERED = 'delivered'; // Delivered / Delivered

    case REJECTED = 'rejected'; // Rejected

    case FINISHED = 'finished'; // Finished / Closed

    case COOKING_COMPLETED = 'cooking_completed'; // CookingCompleted

    public static function fromIikoOrderStatus(IIkoOrderStatus $orderStatus): self
    {
        return match ($orderStatus) {
            IIkoOrderStatus::UNCONFIRMED => self::NEW,
            IIkoOrderStatus::WAIT_COOKING => self::PROCESSING,
            IIkoOrderStatus::CANCELLED => self::CANCELLED,
            IIkoOrderStatus::READY_FOR_COOKING => self::PRODUCE_WAITING,
            IIkoOrderStatus::COOKING_STARTED => self::PRODUCING,
            IIkoOrderStatus::WAITING => self::DELIVERY_WAITING,
            IIkoOrderStatus::ON_WAY => self::DELIVERING,
            IIkoOrderStatus::DELIVERED => self::DELIVERED,
            IIkoOrderStatus::CLOSED => self::FINISHED,
            default => throw new \InvalidArgumentException("Unknown iiko order status: $orderStatus->value"),
        };
    }

    public static function toWelcomeGroupStatus(self $orderStatus): WelcomeGroupOrderStatus
    {
        return match ($orderStatus) {
            self::NEW => WelcomeGroupOrderStatus::NEW,
            self::PROCESSING => WelcomeGroupOrderStatus::PROCESSING,
            self::CANCELLED => WelcomeGroupOrderStatus::CANCELLED,
            self::PRODUCE_WAITING => WelcomeGroupOrderStatus::PRODUCE_WAITING,
            self::PRODUCING => WelcomeGroupOrderStatus::PRODUCING,
            self::DELIVERY_WAITING => WelcomeGroupOrderStatus::DELIVERY_WAITING,
            self::DELIVERING => WelcomeGroupOrderStatus::DELIVERING,
            self::DELIVERED => WelcomeGroupOrderStatus::DELIVERED,
            self::REJECTED => WelcomeGroupOrderStatus::REJECTED,
            self::FINISHED => WelcomeGroupOrderStatus::FINISHED,
            default => throw new \InvalidArgumentException("Unknown order status: $orderStatus->value"),
        };
    }
}

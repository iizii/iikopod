<?php

declare(strict_types=1);

namespace Domain\Orders\Enums;

use Domain\Iiko\Enums\OrderStatus as IIkoOrderStatus;
use Domain\WelcomeGroup\Enums\OrderStatus as WelcomeGroupOrderStatus;

enum OrderStatus: string
{
    case NEW = 'new'; // Unconfirmed

    case PRODUCE_WAITING = 'produce_waiting'; // WaitCooking

    case PROCESSING = 'processing'; // ReadyForCooking

    case PRODUCING = 'producing'; // CookingStarted

    case DELIVERY_WAITING = 'delivery_waiting'; // CookingCompleted / Waiting

    case DELIVERING = 'delivering'; // OnWay

    case DELIVERED = 'delivered'; // Delivered

    case FINISHED = 'finished'; // Closed

    case CANCELLED = 'cancelled'; // Cancelled

    case REJECTED = 'rejected'; // Rejected

//    case COOKING_COMPLETED = 'cooking_completed'; // CookingCompleted

    public static function fromIikoOrderStatus(IIkoOrderStatus $orderStatus): self
    {
        return match ($orderStatus) {
            IIkoOrderStatus::UNCONFIRMED => self::NEW, // 1. Unconfirmed → new
            IIkoOrderStatus::WAIT_COOKING => self::PRODUCE_WAITING, // 2. WaitCooking → produce_waiting
            IIkoOrderStatus::READY_FOR_COOKING => self::PROCESSING, // 3. ReadyForCooking → processing
            IIkoOrderStatus::COOKING_STARTED => self::PRODUCING, // 4. CookingStarted → producing
            IIkoOrderStatus::COOKING_COMPLETED, IIkoOrderStatus::WAITING => self::DELIVERY_WAITING, // 5, 6. CookingCompleted / Waiting → delivery_waiting
            IIkoOrderStatus::ON_WAY => self::DELIVERING, // 7. OnWay → delivering
            IIkoOrderStatus::DELIVERED => self::DELIVERED, // 8. Delivered → delivered
            IIkoOrderStatus::CLOSED => self::FINISHED, // 9. Closed → finished
            IIkoOrderStatus::CANCELLED => self::CANCELLED, // 10. Cancelled → cancelled
            default => throw new \InvalidArgumentException("Unknown iiko order status: $orderStatus->value"),
        };
    }

    public static function toWelcomeGroupStatus(self $orderStatus): WelcomeGroupOrderStatus
    {
        return match ($orderStatus) {
            self::NEW => WelcomeGroupOrderStatus::NEW,
            self::PRODUCE_WAITING => WelcomeGroupOrderStatus::PRODUCE_WAITING,
            self::PROCESSING => WelcomeGroupOrderStatus::PROCESSING,
            self::PRODUCING => WelcomeGroupOrderStatus::PRODUCING,
            self::DELIVERY_WAITING => WelcomeGroupOrderStatus::DELIVERY_WAITING,
            self::DELIVERING => WelcomeGroupOrderStatus::DELIVERING,
            self::DELIVERED => WelcomeGroupOrderStatus::DELIVERED,
            self::FINISHED => WelcomeGroupOrderStatus::FINISHED,
            self::CANCELLED => WelcomeGroupOrderStatus::CANCELLED,
            self::REJECTED => WelcomeGroupOrderStatus::REJECTED,
            default => throw new \InvalidArgumentException("Unknown order status: $orderStatus->value"),
        };
    }
}

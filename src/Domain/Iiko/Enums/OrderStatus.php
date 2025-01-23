<?php

declare(strict_types=1);

namespace Domain\Iiko\Enums;

enum OrderStatus: string
{
    case UNCONFIRMED = 'Unconfirmed';
    case WAIT_COOKING = 'WaitCooking';
    case READY_FOR_COOKING = 'ReadyForCooking';
    case COOKING_STARTED = 'CookingStarted';
    case COOKING_COMPLETED = 'CookingCompleted';
    case WAITING = 'Waiting';
    case ON_WAY = 'OnWay';
    case DELIVERED = 'Delivered';
    case CLOSED = 'Closed';
    case CANCELLED = 'Cancelled';
}

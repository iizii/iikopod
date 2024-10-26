<?php

declare(strict_types=1);

namespace Domain\Iiko\Enums;

use Domain\Iiko\Events\DeliveryOrderError;
use Domain\Iiko\Events\DeliveryOrderUpdate;
use Domain\Iiko\Events\StopListUpdateEvent;

enum WebhookEventType: string
{
    case DELIVERY_ORDER_UPDATE = 'DeliveryOrderUpdate';

    case DELIVERY_ORDER_ERROR = 'DeliveryOrderError';

    case STOP_LIST_UPDATE = 'StopListUpdate';

    /**
     * @return array<non-empty-string, class-string>
     */
    public static function eventMap(): array
    {
        return [
            self::DELIVERY_ORDER_UPDATE->value => DeliveryOrderUpdate::class,
            self::DELIVERY_ORDER_ERROR->value => DeliveryOrderError::class,
            self::STOP_LIST_UPDATE->value => StopListUpdateEvent::class,
        ];
    }
}

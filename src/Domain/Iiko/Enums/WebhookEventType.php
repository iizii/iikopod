<?php

declare(strict_types=1);

namespace Domain\Iiko\Enums;

use Application\Iiko\Events\DeliveryOrderErrorEvent;
use Application\Iiko\Events\DeliveryOrderUpdateEvent;
use Application\Iiko\Events\StopListUpdateEvent;

enum WebhookEventType: string
{
    case DELIVERY_ORDER_UPDATE = 'DeliveryOrderUpdate';

    case DELIVERY_ORDER_ERROR = 'DeliveryOrderError';

    case STOP_LIST_UPDATE = 'StopListUpdate';

    case RESERVE_UPDATE = 'ReserveUpdate';

    case PERSONAL_SHIFT = 'PersonalShift';

    /**
     * @return array<non-empty-string, class-string>
     */
    public static function eventMap(): array
    {
        return [
            self::DELIVERY_ORDER_UPDATE->value => DeliveryOrderUpdateEvent::class,
            //self::DELIVERY_ORDER_ERROR->value => DeliveryOrderErrorEvent::class,
            self::STOP_LIST_UPDATE->value => StopListUpdateEvent::class,
        ];
    }
}

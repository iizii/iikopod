<?php

declare(strict_types=1);

namespace Domain\Iiko\Enums;

enum WebhookEventType: string
{
    case DELIVERY_ORDER_UPDATE = 'DeliveryOrderUpdate';

    case DELIVERY_ORDER_ERROR = 'DeliveryOrderError';

    case STOP_LIST_UPDATE = 'StopListUpdate';
}

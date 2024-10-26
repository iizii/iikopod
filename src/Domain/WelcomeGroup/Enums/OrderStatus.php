<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum OrderStatus: string
{
    case NEW = 'new';

    case PROCESSING = 'processing';

    case CANCELLED = 'cancelled';

    case PRODUCE_WAITING = 'produce_waiting';

    case DELIVERY_WAITING = 'delivery_waiting';

    case DELIVERING = 'delivering';

    case DELIVERED = 'delivered';

    case REJECTED = 'rejected';

    case FINISHED = 'finished';
}

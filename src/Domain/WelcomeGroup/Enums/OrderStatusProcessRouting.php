<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum OrderStatusProcessRouting: string
{
    case WAITING = 'waiting';

    case WORK = 'work';

    case RESPONSE_RECEIVED = 'response_received';

    case FINISH = 'finish';
}

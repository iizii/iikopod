<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum RestaurantStatus: string
{
    case NEW = 'new';

    case ACTIVE = 'active';

    case STOPPED = 'stopped';

    case EMERGENCY = 'emergency';

    case BLOCKED = 'blocked';
}

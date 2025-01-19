<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum OrderSource: int
{
    case TEST = 0;

    case WELCOME_DELIVERY = 3;
}

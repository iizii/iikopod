<?php

declare(strict_types=1);

namespace Domain\Orders\Enums;

enum OrderSource: string
{
    case IIKO = 'iiko';

    case WELCOME_GROUP = 'welcome_group';
}

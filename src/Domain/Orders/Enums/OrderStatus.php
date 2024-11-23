<?php

declare(strict_types=1);

namespace Domain\Orders\Enums;

enum OrderStatus: string
{
    case NEW = 'new';
}

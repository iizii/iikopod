<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum OrderPaymentStatus: string
{
    case NEW = 'new';

    case PROCESSING = 'processing';

    case CANCELLED = 'cancelled';

    case FINISHED = 'finished';
}

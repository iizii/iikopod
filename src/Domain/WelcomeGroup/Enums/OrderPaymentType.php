<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Enums;

enum OrderPaymentType: string
{
    case CASH = 'cash';

    case CARD = 'card';

    case ONLINE = 'online';

    case BONUS = 'bonus';

    case INTERNET = 'internet';

    case PREPAY_ONLINE = 'prepay_online';

    case BANK_ACCOUNT = 'bank_account';
}

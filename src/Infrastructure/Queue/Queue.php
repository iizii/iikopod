<?php

declare(strict_types=1);

namespace Infrastructure\Queue;

enum Queue: string
{
    case DEFAULT = 'default';

    case INTEGRATIONS = 'integrations';

    case STOP_LIST = 'stoplist';
}

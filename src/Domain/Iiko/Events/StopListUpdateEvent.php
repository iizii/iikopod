<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Interfacrs\WebhookEventInterface;

final class StopListUpdateEvent implements WebhookEventInterface
{
    public function __construct() {}
}

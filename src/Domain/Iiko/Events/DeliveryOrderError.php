<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Interfaces\WebhookEventInterface;

final class DeliveryOrderError implements WebhookEventInterface
{
    public function __construct() {}
}

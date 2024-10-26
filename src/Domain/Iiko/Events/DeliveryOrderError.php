<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Interfacrs\WebhookEventInterface;

final class DeliveryOrderError implements WebhookEventInterface
{
    public function __construct() {}
}

<?php

declare(strict_types=1);

namespace Application\Iiko\Events;

use Domain\Iiko\Interfaces\WebhookEventInterface;

final class DeliveryOrderUpdateEvent implements WebhookEventInterface
{
    public function __construct() {}
}

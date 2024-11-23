<?php

declare(strict_types=1);

namespace Application\Iiko\Events;

use Domain\Iiko\Interfaces\WebhookEventInterface;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\EventData;

final readonly class DeliveryOrderUpdateEvent implements WebhookEventInterface
{
    public function __construct(public EventData $eventData) {}
}

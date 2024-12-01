<?php

declare(strict_types=1);

namespace Application\Iiko\Factories;

use Domain\Iiko\Enums\WebhookEventType;
use Domain\Iiko\Exceptions\IikoEventTypeNotFountException;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\EventData;
use Presentation\Api\Requests\IikoWebhookRequest;
use Spatie\LaravelData\Data;

final readonly class WebhookEventDataFactory
{
    public function fromRequest(IikoWebhookRequest $request): Data
    {
        $eventMap = WebhookEventType::eventMap();

        if (! array_key_exists($request->eventType->value, $eventMap)) {
            throw new IikoEventTypeNotFountException();
        }

        return EventData::from($request->eventInfo);
    }
}

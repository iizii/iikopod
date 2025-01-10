<?php

declare(strict_types=1);

namespace Application\Iiko\Factories;

use Domain\Iiko\Enums\WebhookEventType;
use Domain\Iiko\Exceptions\IikoEventTypeNotFountException;
use Presentation\Api\DataTransferObjects;
use Presentation\Api\Requests\IikoWebhookRequest;
use Spatie\LaravelData\Data;

final readonly class WebhookEventDataFactory
{
    public function fromRequest(IikoWebhookRequest $request): Data
    {
        return match ($request->eventType->value) {
            WebhookEventType::DELIVERY_ORDER_UPDATE->value => DataTransferObjects\DeliveryOrderUpdateData\EventData::from($request->eventInfo),
            WebhookEventType::STOP_LIST_UPDATE->value => DataTransferObjects\StopListUpdateData\EventData::from(['organizationId' => $request->organizationId, 'items' => $request->eventInfo['terminalGroupsStopListsUpdates']]),
            default => throw new IikoEventTypeNotFountException(),
        };
    }
}

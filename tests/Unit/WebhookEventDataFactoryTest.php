<?php

declare(strict_types=1);

use Application\Iiko\Factories\WebhookEventDataFactory;
use Carbon\CarbonImmutable;
use Domain\Iiko\Enums\WebhookEventType;
use Domain\Iiko\Exceptions\IikoEventTypeNotFoundException;
use Presentation\Api\Requests\IikoWebhookRequest;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\EventData as DeliveryOrderUpdateEventData;
use Presentation\Api\DataTransferObjects\StopListUpdateData\EventData as StopListUpdateEventData;
use Mockery;

afterEach(function (): void {
    Mockery::close();
});

test('known event types return the correct data object class', function () {
    $factory = new WebhookEventDataFactory();

    $deliveryRequest = new IikoWebhookRequest(
        WebhookEventType::DELIVERY_ORDER_UPDATE,
        CarbonImmutable::now(),
        'org',
        'corr',
        ['key' => 'value']
    );

    $stopListRequest = new IikoWebhookRequest(
        WebhookEventType::STOP_LIST_UPDATE,
        CarbonImmutable::now(),
        'org',
        'corr',
        ['terminalGroupsStopListsUpdates' => []]
    );

    $deliveryData = new stdClass();
    $stopListData = new stdClass();

    Mockery::mock('alias:'.DeliveryOrderUpdateEventData::class)
        ->shouldReceive('from')
        ->once()
        ->with($deliveryRequest->eventInfo)
        ->andReturn($deliveryData);

    Mockery::mock('alias:'.StopListUpdateEventData::class)
        ->shouldReceive('from')
        ->once()
        ->with(['organizationId' => $stopListRequest->organizationId, 'items' => $stopListRequest->eventInfo['terminalGroupsStopListsUpdates']])
        ->andReturn($stopListData);

    $resultDelivery = $factory->fromRequest($deliveryRequest);
    $resultStopList = $factory->fromRequest($stopListRequest);

    expect($resultDelivery)->toBe($deliveryData);
    expect($resultStopList)->toBe($stopListData);
});

test('unknown event type throws exception', function () {
    $factory = new WebhookEventDataFactory();

    $request = new IikoWebhookRequest(
        WebhookEventType::RESERVE_UPDATE,
        CarbonImmutable::now(),
        'org',
        'corr',
        []
    );

    $factory->fromRequest($request);
})->throws(IikoEventTypeNotFoundException::class);

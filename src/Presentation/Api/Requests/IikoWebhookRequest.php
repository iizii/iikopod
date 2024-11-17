<?php

declare(strict_types=1);

namespace Presentation\Api\Requests;

use Carbon\CarbonImmutable;
use Domain\Iiko\Enums\WebhookEventType;
use Spatie\LaravelData\Data;

final class IikoWebhookRequest extends Data
{
    /**
     * @param  non-empty-array<mixed>  $eventInfo
     */
    public function __construct(
        public readonly WebhookEventType $eventType,
        public readonly CarbonImmutable $eventTime,
        public readonly string $organizationId,
        public readonly string $correlationId,
        public readonly array $eventInfo,
    ) {}
}

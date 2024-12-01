<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class EventData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $posId,
        public readonly ?string $externalNumber,
        public readonly string $organizationId,
        public readonly int $timestamp,
        public readonly string $creationStatus,
        public readonly ?ErrorInfo $errorInfo,
        public readonly Order $order
    ) {}
}

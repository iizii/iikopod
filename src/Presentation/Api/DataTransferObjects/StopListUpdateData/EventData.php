<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\StopListUpdateData;

use Spatie\LaravelData\Data;

final class EventData extends Data
{
    public function __construct(
        public readonly string $organizationId,
        public readonly array $items,
    ) {}
}

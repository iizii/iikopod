<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class ErrorInfo extends Data
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly string $description,
    ) {}
}

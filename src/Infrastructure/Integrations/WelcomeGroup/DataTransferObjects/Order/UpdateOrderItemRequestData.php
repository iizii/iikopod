<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order;

use Spatie\LaravelData\Data;

final class UpdateOrderItemRequestData extends Data
{
    public function __construct(
        public readonly string $status,
    ) {}
}

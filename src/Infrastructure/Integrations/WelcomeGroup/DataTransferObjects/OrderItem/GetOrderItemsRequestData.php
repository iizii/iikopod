<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem;

use Spatie\LaravelData\Data;

final class GetOrderItemsRequestData extends Data
{
    public function __construct(
        public readonly int $order,
    ) {}
}

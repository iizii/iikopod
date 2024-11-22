<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order;

use Spatie\LaravelData\Data;

final class CreateOrderData extends Data
{
    public function __construct(
        public readonly int $restaurant,
        public readonly int $client,
        public readonly int $phone,
    ) {}
}

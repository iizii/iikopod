<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order;

use Domain\WelcomeGroup\Enums\OrderStatus;
use Spatie\LaravelData\Data;

final class UpdateOrderRequestData extends Data
{
    public function __construct(
        public readonly OrderStatus $status,
    ) {}
}

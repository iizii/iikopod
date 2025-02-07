<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order;

use Domain\WelcomeGroup\Enums\OrderStatus;
use Spatie\LaravelData\Data;

final class CreateOrderRequestData extends Data
{
    /**
     * @param int $restaurant
     * @param int $client
     * @param int $phone
     * @param int $address
     * @param array<array-key, int> $promotions
     * @param OrderStatus $status
     * @param int $duration
     * @param int $discount
     * @param string|null $comment
     * @param int $source
     * @param int $destination
     * @param bool $isPreorder
     * @param string|null $timePreorder
     */
    public function __construct(
        public readonly int $restaurant,
        public readonly int $client,
        public readonly int $phone,
        public readonly int $address,
        public readonly array $promotions,
        public readonly OrderStatus $status,
        public readonly int $duration,
        public readonly int $discount,
        public readonly ?string $comment,
        public readonly int $source,
        public readonly int $destination,
        public readonly bool $isPreorder = false,
        public readonly ?string $timePreorder = null,
    ) {}
}

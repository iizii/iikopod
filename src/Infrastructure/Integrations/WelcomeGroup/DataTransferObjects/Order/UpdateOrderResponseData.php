<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order;

use Shared\Infrastructure\Integrations\ResponseData;

final class UpdateOrderResponseData extends ResponseData
{
    /**
     * @param  array<array-key, int>  $promotions
     */
    public function __construct(
        public readonly int $id,
        public readonly int $restaurant,
        public readonly int $client,
        public readonly int $phone,
        public readonly int $address,
        public readonly array $promotions,
        public readonly string $status,
        public readonly int $duration,
        public readonly int $discount,
        public readonly ?string $comment,
        public readonly int $source,
        public readonly bool $isPreorder = false,
    ) {}
}

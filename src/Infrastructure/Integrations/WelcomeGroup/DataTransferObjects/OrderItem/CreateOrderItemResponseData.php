<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateOrderItemResponseData extends ResponseData
{
    public function __construct(
        public string $status,
        public ?string $comment,
        public int $food,
        public int $order,
        public int $id,
        public string $created,
        public string $updated,
        public float $price,
        public float $discount,
        public float $sum,
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem;

use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems\FoodModifiersArray;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems\FoodObject;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetOrderItemsResponseData extends ResponseData
{
    /**
     * @param  int[]  $foodModifiers
     * @param  array<array-key, FoodModifiersArray>  $FoodModifiersArray
     */
    public function __construct(
        public readonly ?string $statusComment,
        public readonly string $status,
        public readonly ?string $comment,
        public readonly int $food,
        public readonly ?array $foodModifiers,
        public readonly int $order,
        public readonly int $id,
        public readonly string $created,
        public readonly string $updated,
        public readonly float $price,
        public readonly float $discount,
        public readonly float $sum,
        public readonly bool $isInternetPayment,
        public readonly FoodObject $foodObject,
        public readonly ?array $FoodModifiersArray
    ) {}
}

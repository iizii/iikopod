<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateRestaurantFoodRequestData extends ResponseData
{
    public function __construct(
        public readonly int $restaurant,
        public readonly int $food,
        public readonly string $status = 'active',
    ) {}
}

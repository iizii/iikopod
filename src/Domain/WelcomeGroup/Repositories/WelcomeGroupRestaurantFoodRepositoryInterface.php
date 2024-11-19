<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\RestaurantFood;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupRestaurantFoodRepositoryInterface
{
    public function save(RestaurantFood $restaurantFood): RestaurantFood;

    public function findById(IntegerId $integerId): ?RestaurantFood;
}

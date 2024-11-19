<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\RestaurantFood;
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantFoodRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantFood;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupRestaurantFood>
 */
final class WelcomeGroupRestaurantFoodRepository extends AbstractPersistenceRepository implements WelcomeGroupRestaurantFoodRepositoryInterface
{
    public function save(RestaurantFood $restaurantFood): RestaurantFood
    {
        $welcomeGroupRestaurantFood = new WelcomeGroupRestaurantFood();

        $welcomeGroupRestaurantFood->fromDomainEntity($restaurantFood);
        $welcomeGroupRestaurantFood->save();

        return WelcomeGroupRestaurantFood::toDomainEntity($welcomeGroupRestaurantFood);
    }

    public function findById(IntegerId $integerId): ?RestaurantFood
    {
        return $this
            ->query()
            ->find($integerId->id)
            ?->toDomainEntity();
    }
}

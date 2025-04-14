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

    public function update(RestaurantFood $restaurantFood): RestaurantFood
    {
        $welcomeGroupRestaurantFood = new WelcomeGroupRestaurantFood();

        $welcomeGroupRestaurantFood->fromDomainEntity($restaurantFood);
        $welcomeGroupRestaurantFood->id = $restaurantFood->id->id;
        $welcomeGroupRestaurantFood->save();

        return WelcomeGroupRestaurantFood::toDomainEntity($welcomeGroupRestaurantFood);
    }

    public function findById(IntegerId $integerId): ?RestaurantFood
    {
        $result = $this
            ->query()
            ->find($integerId->id);

        if (! $result) {
            return null;
        }

        return WelcomeGroupRestaurantFood::toDomainEntity($result);
    }

    public function findByExternalFoodId(IntegerId $id): ?RestaurantFood
    {
        $result = $this
            ->query()
            ->where('food_id', $id->id)
            ->first();

        if (! $result) {
            return null;
        }

        return WelcomeGroupRestaurantFood::toDomainEntity($result);
    }

    public function findByInternalFoodAndRestaurantId(IntegerId $internalFoodid, IntegerId $internalRestaurantId): ?RestaurantFood
    {
        $result = $this
            ->query()
            ->where('food_id', $internalFoodid->id)
            ->where('restaurant_id', $internalRestaurantId->id)
            ->first();

        if (! $result) {
            return null;
        }

        return WelcomeGroupRestaurantFood::toDomainEntity($result);
    }
}

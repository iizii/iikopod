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

        return WelcomeGroupRestaurantFood::toDomainEntityStatic($welcomeGroupRestaurantFood);
    }

    public function update(RestaurantFood $restaurantFood): ?RestaurantFood
    {
        $welcomeGroupRestaurantFood = $this->query()
            ->find($restaurantFood->id->id) ?? new WelcomeGroupRestaurantFood();

        if (!$welcomeGroupRestaurantFood) {
            return null;
        }

        $welcomeGroupRestaurantFood->fromDomainEntity($restaurantFood);
//        $welcomeGroupRestaurantFood->id = $restaurantFood->id->id;
        $welcomeGroupRestaurantFood->save();

        return WelcomeGroupRestaurantFood::toDomainEntityStatic($welcomeGroupRestaurantFood);
    }

    public function findById(IntegerId $integerId): ?RestaurantFood
    {
        $result = $this
            ->query()
            ->find($integerId->id);

        if (! $result) {
            return null;
        }

        return WelcomeGroupRestaurantFood::toDomainEntityStatic($result);
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

        return WelcomeGroupRestaurantFood::toDomainEntityStatic($result);
    }

    public function findByInternalFoodAndRestaurantId(IntegerId $internalFoodid, IntegerId $internalRestaurantId): ?RestaurantFood
    {
        logger('findig ids', ['a' => $internalFoodid->id, 'b' => $internalRestaurantId->id]);
        $result = $this
            ->query()
            ->where('welcome_group_food_id', $internalFoodid->id)
            ->where('welcome_group_restaurant_id', $internalRestaurantId->id)
            ->first();
        logger('restFood', ['res' => $result]);
        if (! $result) {
            return null;
        }

        return WelcomeGroupRestaurantFood::toDomainEntityStatic($result);
    }
}

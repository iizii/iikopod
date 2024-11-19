<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\RestaurantModifier;
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantModifierRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantModifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupRestaurantModifier>
 */
final class WelcomeGroupRestaurantModifierRepository extends AbstractPersistenceRepository implements WelcomeGroupRestaurantModifierRepositoryInterface
{
    public function save(RestaurantModifier $restaurantModifier): RestaurantModifier
    {
        $welcomeGroupRestaurantModifier = new WelcomeGroupRestaurantModifier();

        $welcomeGroupRestaurantModifier->fromDomainEntity($restaurantModifier);
        $welcomeGroupRestaurantModifier->save();

        return WelcomeGroupRestaurantModifier::toDomainEntity($welcomeGroupRestaurantModifier);
    }

    public function findById(IntegerId $integerId): ?RestaurantModifier
    {
        return $this
            ->query()
            ->find($integerId->id)
            ?->toDomainEntity();
    }
}

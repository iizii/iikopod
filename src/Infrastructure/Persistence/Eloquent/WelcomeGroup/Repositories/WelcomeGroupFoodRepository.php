<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupFood>
 */
final class WelcomeGroupFoodRepository extends AbstractPersistenceRepository implements WelcomeGroupFoodRepositoryInterface
{
    public function save(Food $food): Food
    {
        $welcomeGroupFoodCategory = new WelcomeGroupFood();

        $welcomeGroupFoodCategory->fromDomainEntity($food);
        $welcomeGroupFoodCategory->save();

        return WelcomeGroupFood::toDomainEntity($welcomeGroupFoodCategory);
    }
}

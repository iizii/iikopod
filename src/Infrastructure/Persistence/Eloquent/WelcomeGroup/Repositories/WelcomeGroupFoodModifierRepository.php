<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodModifier;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodModifier;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupFoodModifier>
 */
final class WelcomeGroupFoodModifierRepository extends AbstractPersistenceRepository implements WelcomeGroupFoodModifierRepositoryInterface
{
    public function save(FoodModifier $modifierType): FoodModifier
    {
        $welcomeGroupFoodModifier = new WelcomeGroupFoodModifier();

        $welcomeGroupFoodModifier->fromDomainEntity($modifierType);
        $welcomeGroupFoodModifier->save();

        return WelcomeGroupFoodModifier::toDomainEntity($welcomeGroupFoodModifier);
    }
}

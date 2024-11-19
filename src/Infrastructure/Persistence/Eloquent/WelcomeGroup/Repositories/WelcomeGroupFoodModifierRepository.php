<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodModifier;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodModifier;
use Shared\Domain\ValueObjects\IntegerId;
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

    public function findByInternalFoodAndModifierIds(IntegerId $internalFoodId, IntegerId $internalModifierId): FoodModifier
    {
        // TODO: Implement findByInternalFoodAndModifierIds() method.
    }

    public function findByExternalFoodAndModifierIds(IntegerId $externalFoodId, IntegerId $externalModifierId): FoodModifier
    {
        // TODO: Implement findByExternalFoodAndModifierIds() method.
    }

    public function findExtetnalId(IntegerId $externalId): FoodModifier
    {
        // TODO: Implement findExtetnalId() method.
    }

    public function findById(IntegerId $id): FoodModifier
    {
        // TODO: Implement findById() method.
    }
}

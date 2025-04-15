<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodModifier;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Exception;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodModifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupFoodModifier>
 */
final class WelcomeGroupFoodModifierRepository extends AbstractPersistenceRepository implements WelcomeGroupFoodModifierRepositoryInterface
{
    public function save(FoodModifier $foodModifier): FoodModifier
    {
        $welcomeGroupFoodModifier = new WelcomeGroupFoodModifier();

        $welcomeGroupFoodModifier->fromDomainEntity($foodModifier);
        $welcomeGroupFoodModifier->save();

        return WelcomeGroupFoodModifier::toDomainEntityStatic($welcomeGroupFoodModifier);
    }

    public function findByInternalFoodAndModifierIds(IntegerId $internalFoodId, IntegerId $internalModifierId): ?FoodModifier
    {
        $result = $this
            ->query()
            ->where('welcome_group_food_id', $internalFoodId->id)
            ->where('welcome_group_modifier_id', $internalModifierId->id)
            ->first();

        if (! $result) {
            return null;
        }

        return WelcomeGroupFoodModifier::toDomainEntityStatic($result);

    }

    public function findByExternalFoodAndModifierIds(IntegerId $externalFoodId, IntegerId $externalModifierId): ?FoodModifier
    {
        $result = $this
            ->query()
            ->where('external_food_id', $externalFoodId)
            ->where('external_modifier_id', $externalModifierId)
            ->first();

        if (! $result) {
            return null;
        }

        return WelcomeGroupFoodModifier::toDomainEntityStatic($result);

    }

    public function findExtetnalId(IntegerId $externalId): ?FoodModifier
    {
        $result = $this
            ->query()
            ->where('external_id', $externalId->id)
            ->first();

        if (! $result) {
            return null;
        }

        return WelcomeGroupFoodModifier::toDomainEntityStatic($result);

    }

    public function findById(IntegerId $id): ?FoodModifier
    {
        $result = $this
            ->query()
            ->find($id->id);

        if (! $result) {
            return null;
        }

        return WelcomeGroupFoodModifier::toDomainEntityStatic($result);

    }

    public function deleteByInternalId(IntegerId $id): ?bool
    {
        return $this
            ->query()
            ->find($id->id)
            ->delete();
    }

    /**
     * @throws Exception
     */
    public function update(FoodModifier $foodModifier): FoodModifier
    {
        $welcomeGroupFoodModifier = $this->query()->find($foodModifier->id->id);

        if (! $welcomeGroupFoodModifier) {
            throw new Exception('Food Modifier not found');
        }

        $welcomeGroupFoodModifier->fromDomainEntity($foodModifier);
        $welcomeGroupFoodModifier->save();

        return WelcomeGroupFoodModifier::toDomainEntityStatic($welcomeGroupFoodModifier);
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\ModifierType;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupFood>
 */
final class WelcomeGroupModifierTypeRepository extends AbstractPersistenceRepository implements WelcomeGroupModifierTypeRepositoryInterface
{
    public function save(ModifierType $modifierType): ModifierType
    {
        $welcomeGroupModifierType = new WelcomeGroupModifierType();

        $welcomeGroupModifierType->fromDomainEntity($modifierType);
        $welcomeGroupModifierType->save();

        return WelcomeGroupModifierType::toDomainEntity($welcomeGroupModifierType);
    }
}

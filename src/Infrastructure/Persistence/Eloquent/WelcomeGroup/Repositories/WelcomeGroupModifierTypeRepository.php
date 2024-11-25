<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\ModifierType;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType;
use Shared\Domain\ValueObjects\IntegerId;
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

    public function update(ModifierType $modifierType): ModifierType
    {
        $welcomeGroupModifierType = new WelcomeGroupModifierType();

        $welcomeGroupModifierType->fromDomainEntity($modifierType);
        $welcomeGroupModifierType->id = $modifierType->id->id;
        $welcomeGroupModifierType->save();

        return WelcomeGroupModifierType::toDomainEntity($welcomeGroupModifierType);
    }

    public function getByIikoModifierGroupIdAndName(IntegerId $iikoModifierGroupId, string $groupName): Collection
    {
        // TODO: Implement getByIikoGroupIdAndName() method.
    }
}

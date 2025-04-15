<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Modifier;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupModifier>
 */
final class WelcomeGroupModifierRepository extends AbstractPersistenceRepository implements WelcomeGroupModifierRepositoryInterface
{
    public function save(Modifier $modifier): Modifier
    {
        $welcomeGroupModifier = new WelcomeGroupModifier();

        $welcomeGroupModifier->fromDomainEntity($modifier);
        $welcomeGroupModifier->save();

        return WelcomeGroupModifier::toDomainEntity($welcomeGroupModifier);
    }

    public function findByIikoId(IntegerId $id): ?Modifier
    {
        $welcomeGroupModifier = $this
            ->query()
            ->whereHas('iikoModifier', static fn (Builder $builder): Builder => $builder->where('id', $id->id))
            ->first();

        if (! $welcomeGroupModifier) {
            return null;
        }

        return WelcomeGroupModifier::toDomainEntity($welcomeGroupModifier);
    }

    public function update(Modifier $modifier): Modifier
    {
        $welcomeGroupModifier = new WelcomeGroupModifier();

        $welcomeGroupModifier->fromDomainEntity($modifier);
        $welcomeGroupModifier->id = $modifier->id->id;
        $welcomeGroupModifier->save();

        return WelcomeGroupModifier::toDomainEntity($welcomeGroupModifier);
    }

    public function findById(IntegerId $id): ?Modifier
    {
        $result = $this->query()->find($id->id);

        if (! $result) {
            return null;
        }

        return WelcomeGroupModifier::toDomainEntity($result);
    }

    public function findByInternalModifierTypeIdAndIikoExternalId(IntegerId $internalModifierTypeId, StringId $externalIikoModifierId): ?Modifier
    {
        $result = $this
            ->query()
            ->where('welcome_group_modifier_type_id', $internalModifierTypeId->id)
            ->where('iiko_external_modifier_id', $externalIikoModifierId->id)
            ->first();

        if (! $result) {
            return null;
        }

        return WelcomeGroupModifier::toDomainEntity($result);
    }
}

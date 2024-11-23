<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupFood>
 */
final class WelcomeGroupFoodRepository extends AbstractPersistenceRepository implements WelcomeGroupFoodRepositoryInterface
{
    public function save(Food $food): Food
    {
        $welcomeGroupFood = new WelcomeGroupFood();

        $welcomeGroupFood->fromDomainEntity($food);
        $welcomeGroupFood->save();

        return WelcomeGroupFood::toDomainEntity($welcomeGroupFood);
    }

    public function findByIikoId(IntegerId $id): ?Food
    {
        $welcomeGroupFood = $this
            ->query()
            ->whereHas(
                'iikoMenuItem',
                static fn (Builder $builder): Builder => $builder->where('id', $id->id),
            )
            ->first();

        if (! $welcomeGroupFood) {
            return null;
        }

        return WelcomeGroupFood::toDomainEntity($welcomeGroupFood);
    }
}

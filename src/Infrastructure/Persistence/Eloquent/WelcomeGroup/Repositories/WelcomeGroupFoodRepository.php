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

    public function findById(IntegerId $integerId): ?Food
    {
        return $this
            ->query()
            ->find($integerId->id)
            ?->toDomainEntity();
    }

    public function findByIikoItemId(IntegerId $integerId): ?Food
    {
        return $this
            ->query()
            ->where('iiko_menu_item_id', $integerId->id)
            ->first()
            ?->toDomainEntity();
    }

    public function update(Food $food): Food
    {
        /** @var WelcomeGroupFood $currentFood */
        $currentFood = $this
            ->query()
            ->find($food->id);

        $newFood = $currentFood
            ->fromDomainEntity($food);

        $newFood->id = $food->id;
        $newFood->save();

        return WelcomeGroupFood::toDomainEntity($newFood);
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

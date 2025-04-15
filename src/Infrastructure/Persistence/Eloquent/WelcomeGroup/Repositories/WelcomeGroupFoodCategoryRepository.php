<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Exceptions\FoodCategoryNotFoundException;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<WelcomeGroupFoodCategory>
 */
final class WelcomeGroupFoodCategoryRepository extends AbstractPersistenceRepository implements WelcomeGroupFoodCategoryRepositoryInterface
{
    public function save(FoodCategory $foodCategory): FoodCategory
    {
        $welcomeGroupFoodCategory = new WelcomeGroupFoodCategory();

        $welcomeGroupFoodCategory->fromDomainEntity($foodCategory);
        $welcomeGroupFoodCategory->save();

        return WelcomeGroupFoodCategory::toDomainEntityStatic($welcomeGroupFoodCategory);
    }

    public function update(FoodCategory $foodCategory): FoodCategory
    {
        $welcomeGroupFoodCategory = $this->query()->find($foodCategory->id->id);

        if (! $welcomeGroupFoodCategory) {
            throw new FoodCategoryNotFoundException();
        }

        $welcomeGroupFoodCategory->fromDomainEntity($foodCategory);
        $welcomeGroupFoodCategory->save();

        return WelcomeGroupFoodCategory::toDomainEntityStatic($welcomeGroupFoodCategory);
    }

    public function findByIikoMenuItemGroupId(IntegerId $id): ?FoodCategory
    {
        $welcomeGroupFoodCategory = $this
            ->query()
            ->where('iiko_menu_item_group_id', $id->id)
            ->first();

        if (! $welcomeGroupFoodCategory) {
            return null;
        }

        return WelcomeGroupFoodCategory::toDomainEntityStatic($welcomeGroupFoodCategory);
    }
}

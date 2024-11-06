<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\Repositories\IikoMenuItemNutritionRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemNutrition>
 */
final class IikoMenuItemNutritionRepository extends AbstractPersistenceRepository implements IikoMenuItemNutritionRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Nutrition
    {
        $result = $this->findEloquentByExternalId($iikoMenuItemSizeId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemNutrition::toDomainEntity($result);
    }

    public function createOrUpdate(Nutrition $nutrition): Nutrition
    {
        $ikoMenuItemNutrition = $this->findEloquentByExternalId($nutrition->itemSizeId) ?? new IikoMenuItemNutrition();

        $ikoMenuItemNutrition->fromDomainEntity($nutrition);
        $ikoMenuItemNutrition->save();

        return IikoMenuItemNutrition::toDomainEntity($ikoMenuItemNutrition);
    }

    public function findEloquentByExternalId(IntegerId $iikoMenuItemSizeId): ?IikoMenuItemNutrition
    {
        return $this
            ->query()
            ->where('iiko_menu_item_size_id', $iikoMenuItemSizeId->id)
            ->first();
    }
}

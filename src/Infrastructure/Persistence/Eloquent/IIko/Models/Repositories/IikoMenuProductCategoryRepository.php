<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\ProductCategory;
use Domain\Iiko\Repositories\IikoMenuProductCategoryRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuProductCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuProductCategory>
 */
final class IikoMenuProductCategoryRepository extends AbstractPersistenceRepository implements IikoMenuProductCategoryRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?ProductCategory
    {
        $result = $this->findEloquentByMenuIdAndExternalId($iikoMenuId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuProductCategory::toDomainEntity($result);
    }

    public function createOrUpdate(ProductCategory $productCategory): ProductCategory
    {
        $iikoMenuProductCategoty = $this->findEloquentByMenuIdAndExternalId(
            $productCategory->iikoMenuId,
            $productCategory->externalId,
        ) ?? new IIkoMenuProductCategory();

        $iikoMenuProductCategoty->fromDomainEntity($productCategory);
        $iikoMenuProductCategoty->save();

        return IikoMenuProductCategory::toDomainEntity($iikoMenuProductCategoty);
    }

    private function findEloquentByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?IikoMenuProductCategory
    {
        return $this
            ->query()
            ->where('iiko_menu_id', $iikoMenuId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

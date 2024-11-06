<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\TaxCategory;
use Domain\Iiko\Repositories\IikoMenuTaxCategoryRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuTaxCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuTaxCategory>
 */
final class IikoMenuTaxCategoryRepository extends AbstractPersistenceRepository implements IikoMenuTaxCategoryRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?TaxCategory
    {
        $result = $this->findEloquentByMenuIdAndExternalId($iikoMenuId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuTaxCategory::toDomainEntity($result);
    }

    public function createOrUpdate(TaxCategory $taxCategory): TaxCategory
    {
        $iikoMenuTaxCategoty = $this->findEloquentByMenuIdAndExternalId(
            $taxCategory->iikoMenuId,
            $taxCategory->externalId,
        ) ?? new IikoMenuTaxCategory();

        $iikoMenuTaxCategoty->fromDomainEntity($taxCategory);
        $iikoMenuTaxCategoty->save();

        return IikoMenuTaxCategory::toDomainEntity($iikoMenuTaxCategoty);
    }

    private function findEloquentByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?IikoMenuTaxCategory
    {
        return $this
            ->query()
            ->where('iiko_menu_id', $iikoMenuId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

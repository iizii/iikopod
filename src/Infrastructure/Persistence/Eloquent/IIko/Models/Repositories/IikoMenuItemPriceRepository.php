<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Repositories\IikoMenuItemPriceRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemPrice>
 */
final class IikoMenuItemPriceRepository extends AbstractPersistenceRepository implements IikoMenuItemPriceRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Price
    {
        $result = $this->findEloquentByExternalId($iikoMenuItemSizeId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemPrice::toDomainEntity($result);
    }

    public function createOrUpdate(Price $price): Price
    {
        $iikoMenuItemPrice = $this->findEloquentByExternalId(
            $price->itemId
        ) ?? new IikoMenuItemPrice();

        $iikoMenuItemPrice->fromDomainEntity($price);
        $iikoMenuItemPrice->save();

        return IikoMenuItemPrice::toDomainEntity($iikoMenuItemPrice);
    }

    public function findEloquentByExternalId(IntegerId $iikoMenuItemSizeId): ?IikoMenuItemPrice
    {
        return $this
            ->query()
            ->where('iiko_menu_item_size_id', $iikoMenuItemSizeId->id)
            ->first();
    }
}

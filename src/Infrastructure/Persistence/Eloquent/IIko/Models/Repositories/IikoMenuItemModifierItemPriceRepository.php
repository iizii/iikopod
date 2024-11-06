<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemPriceRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItemPrice;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemModifierItem>
 */
final class IikoMenuItemModifierItemPriceRepository extends AbstractPersistenceRepository implements IikoMenuItemModifierItemPriceRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Price
    {
        $result = $this->findEloquentByExternalId($iikoMenuItemSizeId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemModifierItemPrice::toDomainEntity($result);
    }

    public function createOrUpdate(Price $price): Price
    {
        $iikoMenuItemPrice = $this->findEloquentByExternalId(
            $price->itemId
        ) ?? new IikoMenuItemModifierItemPrice();

        $iikoMenuItemPrice->fromDomainEntity($price);
        $iikoMenuItemPrice->save();

        return IikoMenuItemModifierItemPrice::toDomainEntity($iikoMenuItemPrice);
    }

    public function findEloquentByExternalId(IntegerId $iikoMenuItemSizeId): ?IikoMenuItemModifierItemPrice
    {
        return $this
            ->query()
            ->where('iiko_menu_item_modifier_item_id', $iikoMenuItemSizeId->id)
            ->first();
    }
}

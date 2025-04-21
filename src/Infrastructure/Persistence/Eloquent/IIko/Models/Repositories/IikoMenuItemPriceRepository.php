<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Repositories\IikoMenuItemPriceRepositoryInterface;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemPrice>
 */
final class IikoMenuItemPriceRepository extends AbstractPersistenceRepository implements IikoMenuItemPriceRepositoryInterface
{
    public function findFor(ItemSize $itemSize): PriceCollection
    {
        $result = $this
            ->query()
            ->where('iiko_menu_item_size_id', $itemSize->id->id)
            ->get();

        return new PriceCollection(
            $result->map(
                static fn (IikoMenuItemPrice $iikoMenuItemPrice): Price => IikoMenuItemPrice::toDomainEntity(
                    $iikoMenuItemPrice,
                ),
            ),
        );
    }

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
        $iikoMenuItemPrice = $this->findByInternalSizeIdAndPriceCategoryId(
            $price->itemId,
            $price->priceCategoryId
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

    public function findByInternalSizeIdAndPriceCategoryId(IntegerId $iikoMenuItemSizeId, StringId $priceCategoryId): ?IikoMenuItemPrice
    {
        return $this
            ->query()
            ->where('iiko_menu_item_size_id', $iikoMenuItemSizeId->id)
            ->where('price_category_id', $priceCategoryId->id)
            ->first();
    }
}

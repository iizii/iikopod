<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemPriceRepositoryInterface
{
    public function findFor(ItemSize $itemSize): PriceCollection;

    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Price;

    public function createOrUpdate(Price $price): Price;

    public function findByInternalSizeIdAndPriceCategoryId(IntegerId $iikoMenuItemSizeId, StringId $priceCategoryId): ?IikoMenuItemPrice;

}

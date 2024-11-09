<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\ValueObjects\IntegerId;

interface IikoMenuItemPriceRepositoryInterface
{
    public function findFor(ItemSize $itemSize): PriceCollection;

    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Price;

    public function createOrUpdate(Price $price): Price;
}

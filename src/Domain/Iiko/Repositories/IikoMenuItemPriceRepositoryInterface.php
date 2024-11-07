<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Price;
use Shared\Domain\ValueObjects\IntegerId;

interface IikoMenuItemPriceRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Price;

    public function createOrUpdate(Price $price): Price;
}

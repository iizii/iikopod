<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Shared\Domain\ValueObjects\IntegerId;

interface IikoMenuItemNutritionRepositoryInterface
{
    public function findFor(ItemSize $itemSize): NutritionCollection;

    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Nutrition;

    public function createOrUpdate(Nutrition $nutrition): Nutrition;
}

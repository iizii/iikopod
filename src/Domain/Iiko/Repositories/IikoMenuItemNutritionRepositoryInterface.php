<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemNutritionRepositoryInterface
{
    public function findFor(ItemSize $itemSize): NutritionCollection;

    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Nutrition;

    public function createOrUpdate(Nutrition $nutrition): Nutrition;

    public function findByInternalSizeIdAndPriceCategoryId(IntegerId $iikoMenuItemSizeId, StringId $priceCategoryId): ?IikoMenuItemNutrition;

}

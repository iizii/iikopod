<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Iiko\ValueObjects\Menu\Nutrition;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Iiko\ValueObjects\Menu\Price;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class ItemSize extends DomainEntity
{
    /**
     * @param  ItemModifierGroupCollection<array-key, ItemModifierGroup>  $itemModifierGroups
     * @param  PriceCollection<array-key, Price>  $prices
     * @param  NutritionCollection<array-key, Nutrition>  $nutritions
     */
    public function __construct(
        public readonly ?StringId $id,
        public readonly string $sku,
        public readonly string $sizeCode,
        public readonly string $sizeName,
        public readonly bool $isDefault,
        public readonly int $portionWeightGrams,
        public readonly Nutrition $nutritionPerHundredGrams,
        public readonly string $measureUnitType,
        public readonly ?string $buttonImageCroppedUrl,
        public readonly ?string $buttonImageUrl,
        public readonly ItemModifierGroupCollection $itemModifierGroups,
        public readonly PriceCollection $prices,
        public readonly NutritionCollection $nutritions,
    ) {}
}

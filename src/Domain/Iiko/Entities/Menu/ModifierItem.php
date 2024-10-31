<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\AllergenGroupCollection;
use Domain\Iiko\ValueObjects\Menu\Nutrition;
use Domain\Iiko\ValueObjects\Menu\Price;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Domain\Iiko\ValueObjects\Menu\Restriction;
use Domain\Iiko\ValueObjects\Menu\TagCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class ModifierItem extends DomainEntity
{
    /**
     * @param  AllergenGroupCollection<array-key, AllergenGroup>  $allergenGroups
     * @param  TagCollection<array-key, Tag>  $tags
     * @param  PriceCollection<array-key, Price>  $prices
     */
    public function __construct(
        public readonly StringId $id,
        public readonly ?StringId $taxCategoryId,
        public readonly ?StringId $productCategoryId,
        public readonly string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly Restriction $restrictions,
        public readonly Nutrition $nutritionPerHundredGrams,
        public readonly int $portionWeightGrams,
        public readonly bool $hidden,
        public readonly int $position,
        public readonly bool $independentQuantity,
        public readonly ?string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly string $measureUnitType,
        public readonly ?string $buttonImageUrl,
        public readonly AllergenGroupCollection $allergenGroups,
        public readonly TagCollection $tags,
        public readonly PriceCollection $prices,
    ) {}
}

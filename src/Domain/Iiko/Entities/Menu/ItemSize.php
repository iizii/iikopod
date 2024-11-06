<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemSize extends DomainEntity
{
    /**
     * @param  ItemModifierGroupCollection<array-key, ItemModifierGroup>  $itemModifierGroups
     * @param  PriceCollection<array-key, Price>  $prices
     * @param  NutritionCollection<array-key, Nutrition>  $nutritions
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $itemId,
        public readonly ?StringId $externalId,
        public readonly string $sku,
        public readonly ?bool $isDefault,
        public readonly int $weight,
        public readonly string $measureUnitType,
        public readonly ItemModifierGroupCollection $itemModifierGroups,
        public readonly PriceCollection $prices,
        public readonly NutritionCollection $nutritions,
    ) {}

    public static function withId(self $itemSize, IntegerId $id): self
    {
        return new self(
            $id,
            $itemSize->itemId,
            $itemSize->externalId,
            $itemSize->sku,
            $itemSize->isDefault,
            $itemSize->weight,
            $itemSize->measureUnitType,
            $itemSize->itemModifierGroups,
            $itemSize->prices,
            $itemSize->nutritions,
        );
    }

    public static function withItemId(self $itemSize, IntegerId $itemId): self
    {
        return new self(
            $itemSize->id,
            $itemId,
            $itemSize->externalId,
            $itemSize->sku,
            $itemSize->isDefault,
            $itemSize->weight,
            $itemSize->measureUnitType,
            $itemSize->itemModifierGroups,
            $itemSize->prices,
            $itemSize->nutritions,
        );
    }
}

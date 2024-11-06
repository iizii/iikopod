<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemModifierGroup extends DomainEntity
{
    /**
     * @param  ItemCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $itemSizeId,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $splittable,
        public readonly bool $isHidden,
        public readonly bool $childModifiersHaveMinMaxRestrictions,
        public readonly string $sku,
        public readonly ItemCollection $items,
    ) {}

    public static function withId(self $itemModifierGroup, IntegerId $id): self
    {
        return new self(
            $id,
            $itemModifierGroup->itemSizeId,
            $itemModifierGroup->externalId,
            $itemModifierGroup->name,
            $itemModifierGroup->description,
            $itemModifierGroup->splittable,
            $itemModifierGroup->isHidden,
            $itemModifierGroup->childModifiersHaveMinMaxRestrictions,
            $itemModifierGroup->sku,
            $itemModifierGroup->items,
        );
    }

    public static function withItemSizeId(self $itemModifierGroup, IntegerId $itemSizeId): self
    {
        return new self(
            $itemModifierGroup->id,
            $itemSizeId,
            $itemModifierGroup->externalId,
            $itemModifierGroup->name,
            $itemModifierGroup->description,
            $itemModifierGroup->splittable,
            $itemModifierGroup->isHidden,
            $itemModifierGroup->childModifiersHaveMinMaxRestrictions,
            $itemModifierGroup->sku,
            $itemModifierGroup->items,
        );
    }
}

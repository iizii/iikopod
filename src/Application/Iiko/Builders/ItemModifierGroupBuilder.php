<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemModifierGroupBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $itemSizeId,
        private StringId $externalId,
        private string $name,
        private ?string $description,
        private bool $splittable,
        private bool $isHidden,
        private bool $childModifiersHaveMinMaxRestrictions,
        private string $sku,
        private ItemCollection $items,
    ) {}

    public static function fromExisted(ItemModifierGroup $itemModifierGroup): self
    {
        return new self(
            $itemModifierGroup->id,
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

    public function setId(IntegerId $id): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setItemSizeId(IntegerId $itemSizeId): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->itemSizeId = $itemSizeId;

        return $clone;
    }

    public function setExternalId(StringId $externalId): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setName(string $name): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function setDescription(?string $description): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }

    public function setSplittable(bool $splittable): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->splittable = $splittable;

        return $clone;
    }

    public function setIsHidden(bool $isHidden): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->isHidden = $isHidden;

        return $clone;
    }

    public function setChildModifiersHaveMinMaxRestrictions(bool $childModifiersHaveMinMaxRestrictions): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->childModifiersHaveMinMaxRestrictions = $childModifiersHaveMinMaxRestrictions;

        return $clone;
    }

    public function setSku(string $sku): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->sku = $sku;

        return $clone;
    }

    public function setItems(ItemCollection $items): ItemModifierGroupBuilder
    {
        $clone = clone $this;
        $clone->items = $items;

        return $clone;
    }

    public function build(): ItemModifierGroup
    {
        return new ItemModifierGroup(
            $this->id,
            $this->itemSizeId,
            $this->externalId,
            $this->name,
            $this->description,
            $this->splittable,
            $this->isHidden,
            $this->childModifiersHaveMinMaxRestrictions,
            $this->sku,
            $this->items,
        );
    }
}

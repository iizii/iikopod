<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemSizeBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $itemId,
        private ?StringId $externalId,
        private string $sku,
        private ?bool $isDefault,
        private int $weight,
        private string $measureUnitType,
        private ItemModifierGroupCollection $itemModifierGroups,
        private PriceCollection $prices,
        private NutritionCollection $nutritions,
    ) {}

    public static function fromExisted(ItemSize $itemSize): self
    {
        return new self(
            $itemSize->id,
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

    public function setId(IntegerId $id): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setItemId(IntegerId $itemId): ItemSizeBuilder
    {
        $clone = clone $this;
        $this->itemId = $itemId;

        return $this;
    }

    public function setExternalId(?StringId $externalId): ItemSizeBuilder
    {
        $clone = clone $this;
        $this->externalId = $externalId;

        return $this;
    }

    public function setSku(string $sku): ItemSizeBuilder
    {
        $clone = clone $this;
        $this->sku = $sku;

        return $this;
    }

    public function setIsDefault(?bool $isDefault): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->isDefault = $isDefault;

        return $clone;
    }

    public function setWeight(int $weight): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->weight = $weight;

        return $clone;
    }

    public function setMeasureUnitType(string $measureUnitType): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->measureUnitType = $measureUnitType;

        return $clone;
    }

    public function setItemModifierGroups(ItemModifierGroupCollection $itemModifierGroups): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->itemModifierGroups = $itemModifierGroups;

        return $clone;
    }

    public function setPrices(PriceCollection $prices): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->prices = $prices;

        return $clone;
    }

    public function setNutritions(NutritionCollection $nutritions): ItemSizeBuilder
    {
        $clone = clone $this;
        $clone->nutritions = $nutritions;

        return $clone;
    }

    public function build(): ItemSize
    {
        return new ItemSize(
            $this->id,
            $this->itemId,
            $this->externalId,
            $this->sku,
            $this->isDefault,
            $this->weight,
            $this->measureUnitType,
            $this->itemModifierGroups,
            $this->prices,
            $this->nutritions,
        );
    }
}

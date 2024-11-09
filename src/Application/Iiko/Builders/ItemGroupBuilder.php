<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemGroupBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $iikoMenuId,
        private StringId $externalId,
        private string $name,
        private ?string $description,
        private bool $isHidden,
        private ItemCollection $items,
    ) {}

    public static function fromExisted(ItemGroup $itemGroup): self
    {
        return new self(
            $itemGroup->id,
            $itemGroup->iikoMenuId,
            $itemGroup->externalId,
            $itemGroup->name,
            $itemGroup->description,
            $itemGroup->isHidden,
            $itemGroup->items,
        );
    }

    public function setId(IntegerId $id): ItemGroupBuilder
    {
        $clone = clone $this;

        $clone->id = $id;

        return $clone;
    }

    public function setIikoMenuId(IntegerId $iikoMenuId): ItemGroupBuilder
    {
        $clone = clone $this;

        $clone->iikoMenuId = $iikoMenuId;

        return $clone;
    }

    public function setExternalId(StringId $externalId): ItemGroupBuilder
    {
        $clone = clone $this;

        $this->externalId = $externalId;

        return $this;
    }

    public function setName(string $name): ItemGroupBuilder
    {
        $clone = clone $this;

        $clone->name = $name;

        return $clone;
    }

    public function setDescription(?string $description): ItemGroupBuilder
    {
        $clone = clone $this;

        $clone->description = $description;

        return $clone;
    }

    public function setIsHidden(bool $isHidden): ItemGroupBuilder
    {
        $clone = clone $this;

        $clone->isHidden = $isHidden;

        return $clone;
    }

    public function setItems(ItemCollection $items): ItemGroupBuilder
    {
        $clone = clone $this;

        $clone->items = $items;

        return $clone;
    }

    public function build(): ItemGroup
    {
        return new ItemGroup(
            $this->id,
            $this->iikoMenuId,
            $this->externalId,
            $this->name,
            $this->description,
            $this->isHidden,
            $this->items,
        );
    }
}

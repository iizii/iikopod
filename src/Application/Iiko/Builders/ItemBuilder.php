<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $itemGroupId,
        private StringId $externalId,
        private string $sku,
        private string $name,
        private ?string $description,
        private ?string $type,
        private ?string $measureUnit,
        private ?string $paymentSubject,
        private bool $isHidden,
        private ?int $weight,
        private PriceCollection $prices,
        private ItemSizeCollection $itemSizes,
        private string $prefix,
    ) {}

    public static function fromExisted(Item $item): self
    {
        return new self(
            $item->id,
            $item->itemGroupId,
            $item->externalId,
            $item->sku,
            $item->name,
            $item->description,
            $item->type,
            $item->measureUnit,
            $item->paymentSubject,
            $item->isHidden,
            $item->weight,
            $item->prices,
            $item->itemSizes,
            ''
        );
    }

    public function setId(IntegerId $id): ItemBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setItemGroupId(IntegerId $itemGroupId): ItemBuilder
    {
        $clone = clone $this;
        $clone->itemGroupId = $itemGroupId;

        return $clone;
    }

    public function setExternalId(StringId $externalId): ItemBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setSku(string $sku): ItemBuilder
    {
        $clone = clone $this;
        $clone->sku = $sku;

        return $clone;
    }

    public function setName(string $name): ItemBuilder
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function setDescription(?string $description): ItemBuilder
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }

    public function setType(?string $type): ItemBuilder
    {
        $clone = clone $this;
        $clone->type = $type;

        return $clone;
    }

    public function setMeasureUnit(?string $measureUnit): ItemBuilder
    {
        $clone = clone $this;
        $clone->measureUnit = $measureUnit;

        return $clone;
    }

    public function setPrefix(string $prefix = ''): ItemBuilder
    {
        $clone = clone $this;
        $clone->prefix = $prefix;

        return $clone;
    }

    public function setPaymentSubject(?string $paymentSubject): ItemBuilder
    {
        $clone = clone $this;
        $clone->paymentSubject = $paymentSubject;

        return $clone;
    }

    public function setIsHidden(bool $isHidden): ItemBuilder
    {
        $clone = clone $this;
        $clone->isHidden = $isHidden;

        return $clone;
    }

    public function setPrices(PriceCollection $prices): ItemBuilder
    {
        $clone = clone $this;
        $clone->prices = $prices;

        return $clone;
    }

    public function setItemSizes(ItemSizeCollection $itemSizes): ItemBuilder
    {
        $clone = clone $this;
        $clone->itemSizes = $itemSizes;

        return $clone;
    }

    public function setWeight(?int $weight): ItemBuilder
    {
        $clone = clone $this;
        $clone->weight = $weight;

        return $clone;
    }

    public function build(): Item
    {
        $prefix = empty($this->prefix) ? '' : $this->prefix.' ';

        return new Item(
            $this->id,
            $this->itemGroupId,
            $this->externalId,
            $this->sku,
            $prefix.$this->name,
            $this->description,
            $this->type,
            $this->measureUnit,
            $this->paymentSubject,
            $this->isHidden,
            $this->weight,
            $this->prices,
            $this->itemSizes,
        );
    }
}

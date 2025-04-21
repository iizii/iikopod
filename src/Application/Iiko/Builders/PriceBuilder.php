<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\Price;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class PriceBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $itemId,
        private StringId $priceCategoryId,
        private ?int $price,
    ) {}

    public static function fromExisted(Price $price): self
    {
        return new self(
            $price->id,
            $price->itemId,
            new StringId(),
            $price->price,
        );
    }

    public function setId(IntegerId $id): PriceBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setPrice(?int $price): PriceBuilder
    {
        $clone = clone $this;
        $clone->price = $price;

        return $clone;
    }

    public function setItemId(IntegerId $itemId): PriceBuilder
    {
        $clone = clone $this;
        $clone->itemId = $itemId;

        return $clone;
    }

    public function setPriceCategoryId(StringId $priceCategoryId): PriceBuilder
    {
        $clone = clone $this;
        $clone->priceCategoryId = $priceCategoryId;

        return $clone;
    }

    public function build(): Price
    {
        return new Price(
            $this->id,
            $this->itemId,
            $this->priceCategoryId,
            $this->price,
        );
    }
}

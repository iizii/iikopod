<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\Price;
use Shared\Domain\ValueObjects\IntegerId;

final class PriceBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $itemId,
        private ?int $price,
    ) {}

    public static function fromExisted(Price $price): self
    {
        return new self(
            $price->id,
            $price->itemId,
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

    public function build(): Price
    {
        return new Price(
            $this->id,
            $this->itemId,
            $this->price,
        );
    }
}

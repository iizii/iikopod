<?php

declare(strict_types=1);

namespace Application\Iiko\Builders;

use Domain\Iiko\Entities\Menu\Nutrition;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class NutritionBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $itemSizeId,
        private StringId $priceCategoryId,
        private float $fats,
        private float $proteins,
        private float $carbs,
        private float $energy,
        private ?float $saturatedFattyAcid,
        private ?float $salt,
        private ?float $sugar,
    ) {}

    public static function fromExisted(Nutrition $nutrition): self
    {
        return new self(
            $nutrition->id,
            $nutrition->itemSizeId,
            new StringId(),
            $nutrition->fats,
            $nutrition->proteins,
            $nutrition->carbs,
            $nutrition->energy,
            $nutrition->saturatedFattyAcid,
            $nutrition->salt,
            $nutrition->sugar,
        );
    }

    public function setId(IntegerId $id): NutritionBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setItemSizeId(IntegerId $itemSizeId): NutritionBuilder
    {
        $clone = clone $this;
        $clone->itemSizeId = $itemSizeId;

        return $clone;
    }

    public function setPriceCategoryId(StringId $priceCategoryId): NutritionBuilder
    {
        $clone = clone $this;
        $clone->priceCategoryId = $priceCategoryId;

        return $clone;
    }

    public function setFats(float $fats): NutritionBuilder
    {
        $clone = clone $this;
        $clone->fats = $fats;

        return $clone;
    }

    public function setProteins(float $proteins): NutritionBuilder
    {
        $clone = clone $this;
        $clone->proteins = $proteins;

        return $clone;
    }

    public function setCarbs(float $carbs): NutritionBuilder
    {
        $clone = clone $this;
        $clone->carbs = $carbs;

        return $clone;
    }

    public function setEnergy(float $energy): NutritionBuilder
    {
        $clone = clone $this;
        $clone->energy = $energy;

        return $clone;
    }

    public function setSaturatedFattyAcid(?float $saturatedFattyAcid): NutritionBuilder
    {
        $clone = clone $this;
        $clone->saturatedFattyAcid = $saturatedFattyAcid;

        return $clone;
    }

    public function setSalt(?float $salt): NutritionBuilder
    {
        $clone = clone $this;
        $clone->salt = $salt;

        return $clone;
    }

    public function setSugar(?float $sugar): NutritionBuilder
    {
        $clone = clone $this;
        $clone->sugar = $sugar;

        return $clone;
    }

    public function build(): Nutrition
    {
        return new Nutrition(
            $this->id,
            $this->itemSizeId,
            $this->priceCategoryId,
            $this->fats,
            $this->proteins,
            $this->carbs,
            $this->energy,
            $this->saturatedFattyAcid,
            $this->salt,
            $this->sugar,
        );
    }
}

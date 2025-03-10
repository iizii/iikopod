<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Builders;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Exceptions\ItemSizeNotLoadedException;
use Domain\Iiko\Exceptions\NutritionNotLoadedException;
use Domain\Iiko\Exceptions\PriceNotLoadedException;
use Domain\WelcomeGroup\Entities\Food;
use Shared\Domain\ValueObjects\IntegerId;

final class FoodBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $iikoItemId,
        private IntegerId $internalFoodCategoryId,
        private IntegerId $externalId,
        private IntegerId $externalFoodCategoryId,
        private IntegerId $workshopId,
        private string $name,
        private ?string $description,
        private int $weight,
        private int $caloricity,
        private ?int $price,
    ) {}

    public static function fromIikoItem(Item $item): self
    {
        /** @var ItemSize|null $itemSize */
        $itemSize = $item->itemSizes->first();

        if (! $itemSize) {
            throw new ItemSizeNotLoadedException(sprintf('Price not loaded for item %s', $item->id->id));
        }

        /** @var Price|null $price */
        $price = $itemSize->prices->first();

        if (! $price) {
            throw new PriceNotLoadedException();
        }

        /** @var Nutrition|null $nutrition */
        $nutrition = $itemSize->nutritions->first();

        if (! $nutrition) {
            throw new NutritionNotLoadedException();
        }

        $caloricity = $nutrition->energy;

        if (is_null($nutrition->energy) || $nutrition->energy <= 0) {
            $caloricity = 1;
        }

        return new self(
            new IntegerId(),
            $item->id,
            new IntegerId(),
            new IntegerId(),
            new IntegerId(),
            new IntegerId(),
            $item->name,
            $item->description,
            $itemSize->weight,
            (int) $caloricity,
            $price->price ?? 0,
        );
    }

    public static function fromExisted(Food $food): self
    {
        return new self(
            $food->id,
            $food->iikoItemId,
            $food->internalFoodCategoryId,
            $food->externalId,
            $food->externalFoodCategoryId,
            $food->workshopId,
            $food->name,
            $food->description,
            $food->weight,
            $food->caloricity,
            $food->price,
        );
    }

    public function setId(IntegerId $id): FoodBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setIikoItemId(IntegerId $iikoItemId): FoodBuilder
    {
        $clone = clone $this;
        $clone->iikoItemId = $iikoItemId;

        return $clone;
    }

    public function setInternalFoodCategoryId(IntegerId $internalFoodCategoryId): FoodBuilder
    {
        $clone = clone $this;
        $clone->internalFoodCategoryId = $internalFoodCategoryId;

        return $clone;
    }

    public function setExternalId(IntegerId $externalId): FoodBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setExternalFoodCategoryId(IntegerId $externalFoodCategoryId): FoodBuilder
    {
        $clone = clone $this;
        $clone->externalFoodCategoryId = $externalFoodCategoryId;

        return $clone;
    }

    public function setWorkshopId(IntegerId $workshopId): FoodBuilder
    {
        $clone = clone $this;
        $clone->workshopId = $workshopId;

        return $clone;
    }

    public function setName(string $name): FoodBuilder
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function setDescription(string $description): FoodBuilder
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }

    public function setWeight(int $weight): FoodBuilder
    {
        $clone = clone $this;
        $clone->weight = $weight;

        return $clone;
    }

    public function setCaloricity(int $caloricity): FoodBuilder
    {
        $clone = clone $this;
        $clone->caloricity = $caloricity;

        return $clone;
    }

    public function setPrice(?int $price): FoodBuilder
    {
        $clone = clone $this;
        $clone->price = $price;

        return $clone;
    }

    public function build(): Food
    {
        return new Food(
            $this->id,
            $this->iikoItemId,
            $this->internalFoodCategoryId,
            $this->externalId,
            $this->externalFoodCategoryId,
            $this->workshopId,
            $this->name,
            $this->description,
            $this->weight,
            $this->caloricity,
            $this->price,
        );
    }
}

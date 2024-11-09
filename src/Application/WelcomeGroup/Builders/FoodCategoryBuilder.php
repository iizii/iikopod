<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Builders;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Shared\Domain\ValueObjects\IntegerId;

final class FoodCategoryBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $iikoItemGroupId,
        private IntegerId $externalId,
        private string $name,
    ) {}

    public static function fromIikoItemGroup(ItemGroup $itemGroup): self
    {
        return new self(
            new IntegerId(),
            $itemGroup->id,
            new IntegerId(),
            $itemGroup->name,
        );
    }

    public static function fromExisted(FoodCategory $foodCategory): self
    {
        return new self(
            $foodCategory->id,
            $foodCategory->iikoItemGroupId,
            $foodCategory->externalId,
            $foodCategory->name,
        );
    }

    public function setId(IntegerId $id): FoodCategoryBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setIikoItemGroupId(IntegerId $iikoItemGroupId): FoodCategoryBuilder
    {
        $clone = clone $this;
        $clone->iikoItemGroupId = $iikoItemGroupId;

        return $clone;
    }

    public function setExternalId(IntegerId $externalId): FoodCategoryBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setName(string $name): FoodCategoryBuilder
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function build(): FoodCategory
    {
        return new FoodCategory(
            $this->id,
            $this->iikoItemGroupId,
            $this->externalId,
            $this->name,
        );
    }
}

<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Builders;

use Domain\WelcomeGroup\Entities\FoodModifier;
use Shared\Domain\ValueObjects\IntegerId;

final class FoodModifierBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $internalFoodId,
        private IntegerId $internalModifierId,
        private IntegerId $externalId,
        private IntegerId $externalFoodId,
        private IntegerId $externalModifierId,
        private int $weight,
        private int $caloricity,
        private int $price,
        private int $duration,
    ) {}

    public static function fromExisted(FoodModifier $foodModifier): self
    {
        return new self(
            $foodModifier->id,
            $foodModifier->internalFoodId,
            $foodModifier->internalModifierId,
            $foodModifier->externalId,
            $foodModifier->externalFoodId,
            $foodModifier->externalModifierId,
            $foodModifier->weight,
            $foodModifier->caloricity,
            $foodModifier->price,
            $foodModifier->duration,
        );
    }

    public function setId(IntegerId $id): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function setInternalFoodId(IntegerId $internalFoodId): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->internalFoodId = $internalFoodId;

        return $clone;
    }

    public function setInternalModifierId(IntegerId $internalModifierId): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->internalModifierId = $internalModifierId;

        return $clone;
    }

    public function setExternalId(IntegerId $externalId): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setExternalFoodId(IntegerId $externalFoodId): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->externalFoodId = $externalFoodId;

        return $clone;
    }

    public function setExternalModifierId(IntegerId $externalModifierId): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->externalModifierId = $externalModifierId;

        return $clone;
    }

    public function setWeight(int $weight): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->weight = $weight;

        return $clone;
    }

    public function setCaloricity(int $caloricity): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->caloricity = $caloricity;

        return $clone;
    }

    public function setPrice(int $price): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->price = $price;

        return $clone;
    }

    public function setDuration(int $duration): FoodModifierBuilder
    {
        $clone = clone $this;
        $clone->duration = $duration;

        return $clone;
    }

    public function build(): FoodModifier
    {
        return new FoodModifier(
            $this->id,
            $this->internalFoodId,
            $this->internalModifierId,
            $this->externalId,
            $this->externalFoodId,
            $this->externalModifierId,
            $this->weight,
            $this->caloricity,
            $this->price,
            $this->duration,
        );
    }
}

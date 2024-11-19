<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodModifier;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupFoodModifierRepositoryInterface
{
    public function save(FoodModifier $modifierType): FoodModifier;

    public function findByInternalFoodAndModifierIds(IntegerId $internalFoodId, IntegerId $internalModifierId): FoodModifier;

    public function findByExternalFoodAndModifierIds(IntegerId $externalFoodId, IntegerId $externalModifierId): FoodModifier;

    public function findExtetnalId(IntegerId $externalId): FoodModifier;

    public function findById(IntegerId $id): FoodModifier;
}

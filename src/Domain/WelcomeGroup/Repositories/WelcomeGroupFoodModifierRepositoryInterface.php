<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodModifier;

interface WelcomeGroupFoodModifierRepositoryInterface
{
    public function save(FoodModifier $modifierType): FoodModifier;
}

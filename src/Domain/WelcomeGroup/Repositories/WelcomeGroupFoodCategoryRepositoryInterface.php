<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\FoodCategory;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupFoodCategoryRepositoryInterface
{
    public function save(FoodCategory $foodCategory): FoodCategory;

    public function update(FoodCategory $foodCategory): FoodCategory;

    public function findByIikoMenuItemGroupId(IntegerId $id): ?FoodCategory;
}

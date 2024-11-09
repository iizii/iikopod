<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Food;

interface WelcomeGroupFoodRepositoryInterface
{
    public function save(Food $food): Food;
}

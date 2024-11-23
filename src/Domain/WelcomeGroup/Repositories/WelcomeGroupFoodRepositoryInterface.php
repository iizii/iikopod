<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Food;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupFoodRepositoryInterface
{
    public function save(Food $food): Food;

    public function findByIikoId(IntegerId $id): ?Food;
}

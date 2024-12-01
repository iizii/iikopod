<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Food;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupFoodRepositoryInterface
{
    public function save(Food $food): Food;

    public function update(Food $food): Food;

    public function findById(IntegerId $integerId): ?Food;

    public function findByIikoItemId(IntegerId $integerId): ?Food;

    public function findByIikoId(IntegerId $id): ?Food;
}

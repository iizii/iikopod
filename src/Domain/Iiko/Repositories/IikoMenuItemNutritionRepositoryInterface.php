<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Nutrition;
use Shared\Domain\ValueObjects\IntegerId;

interface IikoMenuItemNutritionRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemSizeId): ?Nutrition;

    public function createOrUpdate(Nutrition $nutrition): Nutrition;
}

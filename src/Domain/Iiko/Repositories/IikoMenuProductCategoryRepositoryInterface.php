<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ProductCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuProductCategoryRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?ProductCategory;

    public function createOrUpdate(ProductCategory $productCategory): ProductCategory;
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\TaxCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuTaxCategoryRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?TaxCategory;

    public function createOrUpdate(TaxCategory $taxCategory): TaxCategory;
}

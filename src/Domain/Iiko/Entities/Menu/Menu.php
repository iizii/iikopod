<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemGroupCollection;
use Domain\Iiko\ValueObjects\Menu\ProductCategoryCollection;
use Domain\Iiko\ValueObjects\Menu\TaxCategoryCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Menu extends DomainEntity
{
    /**
     * @param  TaxCategoryCollection<array-key, TaxCategory>  $taxCategories
     * @param  ProductCategoryCollection<array-key, ProductCategory>  $productCategories
     * @param  ItemGroupCollection<array-key, ItemGroup>  $itemGroups
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $externalId,
        public readonly int $revision,
        public readonly string $name,
        public readonly ?string $description,
        public readonly TaxCategoryCollection $taxCategories,
        public readonly ProductCategoryCollection $productCategories,
        public readonly ItemGroupCollection $itemGroups,
    ) {}
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ProductCategoryCollection;
use Domain\Iiko\ValueObjects\Menu\PureExternalMenuItemCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Menu extends DomainEntity
{
    /**
     * @param  ProductCategoryCollection<array-key, ProductCategory>  $productCategories
     * @param  PureExternalMenuItemCollection<array-key, PureExternalMenuItemCategory>  $pureExternalMenuItemCategories
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly int $revision,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $buttonImageUrl,
        public readonly ProductCategoryCollection $productCategories,
        public readonly PureExternalMenuItemCollection $pureExternalMenuItemCategories
    ) {}
}

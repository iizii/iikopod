<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\AllergenGroupCollection;
use Domain\Iiko\ValueObjects\Menu\AllergenGroupIdCollection;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\TagCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class Item extends DomainEntity
{
    /**
     * @param  AllergenGroupCollection<array-key, AllergenGroup>  $allergenGroups
     * @param  TagCollection<array-key, Tag>  $tags
     * @param  ItemSizeCollection<array-key, ItemSize>  $itemSizes
     * @param  AllergenGroupIdCollection<array-key, StringId>  $allergenGroupIds
     */
    public function __construct(
        public readonly StringId $id,
        public readonly ?StringId $iikoModifierSchemaId,
        public readonly ?StringId $taxCategoryId,
        public readonly ?StringId $productCategoryId,
        public readonly string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $taxCategory,
        public readonly string $iikoModifierSchemaName,
        public readonly string $type,
        public readonly bool $canBeDivided,
        public readonly bool $canSetOpenPrice,
        public readonly bool $useBalanceForSell,
        public readonly string $measureUnit,
        public readonly string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly bool $isHidden,
        public readonly string $orderItemType,
        public readonly AllergenGroupCollection $allergenGroups,
        public readonly TagCollection $tags,
        public readonly ItemSizeCollection $itemSizes,
        public readonly AllergenGroupIdCollection $allergenGroupIds,
    ) {}
}

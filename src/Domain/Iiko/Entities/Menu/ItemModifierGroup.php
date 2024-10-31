<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ModifierItemCollection;
use Domain\Iiko\ValueObjects\Menu\Restriction;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class ItemModifierGroup extends DomainEntity
{
    /**
     * @param  ModifierItemCollection<array-key, ModifierItem>  $items
     */
    public function __construct(
        public readonly StringId $id,
        public readonly string $name,
        public readonly string $description,
        public readonly Restriction $restriction,
        public readonly bool $canBeDivided,
        public readonly bool $hidden,
        public readonly bool $childModifiersHaveMinMaxRestrictions,
        public readonly string $sku,
        public readonly ModifierItemCollection $items,
    ) {}
}

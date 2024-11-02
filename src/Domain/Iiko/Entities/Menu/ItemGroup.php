<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ItemGroup extends DomainEntity
{
    /**
     * @param  ItemCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $menuId,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $isHidden,
        public readonly ItemCollection $items,
    ) {}
}

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
        public readonly IntegerId $iikoMenuId,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $isHidden,
        public readonly ItemCollection $items,
    ) {}

    public static function withId(self $itemGroup, IntegerId $id): self
    {
        return new self(
            $id,
            $itemGroup->iikoMenuId,
            $itemGroup->externalId,
            $itemGroup->name,
            $itemGroup->description,
            $itemGroup->isHidden,
            $itemGroup->items,
        );
    }

    public static function withMenuId(self $itemGroup, IntegerId $menuId): self
    {
        return new self(
            $itemGroup->id,
            $menuId,
            $itemGroup->externalId,
            $itemGroup->name,
            $itemGroup->description,
            $itemGroup->isHidden,
            $itemGroup->items,
        );
    }
}

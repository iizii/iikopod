<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class PureExternalMenuItemCategory extends DomainEntity
{
    /**
     * @param  ItemCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly StringId $id,
        public readonly ?StringId $scheduleId,
        public readonly ?StringId $iikoGroupId,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $buttonImageUrl,
        public readonly ?string $headerImageUrl,
        public readonly ?string $scheduleName,
        public readonly bool $isHidden,
        public readonly ItemCollection $items,
    ) {}
}

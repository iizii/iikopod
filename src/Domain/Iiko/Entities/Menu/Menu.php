<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Domain\Iiko\ValueObjects\Menu\ItemGroupCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Menu extends DomainEntity
{
    /**
     * @param  ItemGroupCollection<array-key, ItemGroup>  $itemGroups
     */
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $organizationSettingId,
        public readonly StringId $externalId,
        public readonly int $revision,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ItemGroupCollection $itemGroups,
    ) {}
}

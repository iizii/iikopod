<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemModifierGroupRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemSizeId, StringId $externalId): ?ItemModifierGroup;

    public function createOrUpdate(ItemModifierGroup $itemModifierGroup): ItemModifierGroup;
}

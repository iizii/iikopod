<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemModifierGroupRepositoryInterface
{
    public function findFor(ItemSize $itemSize): ItemModifierGroupCollection;

    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemSizeId, StringId $externalId): ?ItemModifierGroup;

    public function createOrUpdate(ItemModifierGroup $itemModifierGroup): ItemModifierGroup;

    public function findEloquentByExternalId(StringId $externalId): ?IikoMenuItemModifierGroup;

}

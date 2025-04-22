<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemModifierItemRepositoryInterface
{
    public function findFor(ItemSize $itemSize): ItemCollection;

    public function findByExternalId(StringId $id, Item $item): ?Item;

    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemModifierGroupId, StringId $externalId): ?Item;

    public function createOrUpdate(Item $item): Item;
}

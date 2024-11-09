<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemSizeRepositoryInterface
{
    public function finById(IntegerId $id): ?ItemSize;

    public function findFor(Item $item): ItemSizeCollection;

    public function findForWithAllRelations(Item $item): ItemSizeCollection;

    public function findByExternalId(IntegerId $iikoMenuItemId, StringId $externalId): ?ItemSize;

    public function createOrUpdate(ItemSize $itemSize): ItemSize;
}

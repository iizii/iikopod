<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemModifierItemRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemModifierGroupId, StringId $externalId): ?Item;

    public function createOrUpdate(Item $item): Item;
}

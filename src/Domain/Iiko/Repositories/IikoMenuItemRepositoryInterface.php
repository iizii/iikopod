<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemGroupId, StringId $externalId): ?Item;

    public function createOrUpdate(Item $item): Item;
}

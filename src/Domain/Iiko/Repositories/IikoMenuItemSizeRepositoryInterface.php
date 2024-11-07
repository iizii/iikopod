<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemSizeRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemId, StringId $externalId): ?ItemSize;

    public function createOrUpdate(ItemSize $itemSize): ItemSize;
}

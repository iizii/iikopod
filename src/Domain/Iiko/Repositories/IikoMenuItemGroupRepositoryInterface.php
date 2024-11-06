<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemGroupRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?ItemGroup;

    public function createOrUpdate(ItemGroup $itemGroup): ItemGroup;
}

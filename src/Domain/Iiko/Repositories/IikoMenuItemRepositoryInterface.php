<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Illuminate\Database\Eloquent\Collection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuItemRepositoryInterface
{
    public function findById(IntegerId $id): ?Item;

    public function findByExternalId(StringId $id): ?Item;

    public function findByExternalIdAndSourceKey(StringId $id, string $sourceKey): ?Item;

    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemGroupId, StringId $externalId): ?Item;

    public function createOrUpdate(Item $item): Item;

    public function update(Item $item): Item;

    /**
     * @return Collection<array-key, IikoMenuItem>
     */
    public function getAllByMenuIds(array $menuIds): Collection;
}

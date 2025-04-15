<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Repositories\IikoMenuItemRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItem>
 */
final class IikoMenuItemRepository extends AbstractPersistenceRepository implements IikoMenuItemRepositoryInterface
{
    public function findById(IntegerId $id): ?Item
    {
        $result = $this->query()->find($id->id);

        if (! $result) {
            return null;
        }

        return IikoMenuItem::toDomainEntity($result);
    }

    public function findByExternalId(StringId $id): ?Item
    {
        $result = $this
            ->query()
            ->where('external_id', $id->id)
            ->first();

        if (! $result) {
            return null;
        }

        return IikoMenuItem::toDomainEntity($result);
    }

    public function findByExternalIdAndSourceKey(StringId $id, string $sourceKey): ?Item
    {
        $result = $this
            ->query()
            ->where('external_id', $id->id)
            ->where('name', 'LIKE', $sourceKey)
            ->first();

        if (! $result) {
            return null;
        }

        return IikoMenuItem::toDomainEntity($result);
    }

    //    public function getAllItemsByExternalId(StringId $id)
    //    {
    //        $result = $this
    //            ->query()
    //            ->where('external_id', $id->id)
    //    }

    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemGroupId, StringId $externalId): ?Item
    {
        $result = $this->findEloquentByMenuIdAndExternalId($iikoMenuItemGroupId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuItem::toDomainEntity($result);
    }

    public function createOrUpdate(Item $item): Item
    {
        $iikoItem = $this->findEloquentByMenuIdAndExternalId(
            $item->itemGroupId,
            $item->externalId,
        ) ?? new IikoMenuItem();

        $iikoItem->fromDomainEntity($item);
        $iikoItem->save();

        return IIkoMenuItem::toDomainEntity($iikoItem);
    }

    public function update(Item $item): Item
    {
        $iikoItem = $this->findEloquentByMenuIdAndExternalId(
            $item->itemGroupId,
            $item->externalId,
        ) ?? new IikoMenuItem();

        $iikoItem->fromDomainEntity($item);
        $iikoItem->save();

        return IIkoMenuItem::toDomainEntity($iikoItem);
    }

    public function getAllByMenuIds(array $menuIds): \Illuminate\Database\Eloquent\Collection
    {
        return $this
            ->query()
            ->whereIn('iiko_menu_item_group_id', $menuIds)
            ->get();
    }

    private function findEloquentByMenuIdAndExternalId(
        IntegerId $iikoMenuItemGroupId,
        StringId $externalId,
    ): ?IikoMenuItem {
        return $this
            ->query()
            ->where('iiko_menu_item_group_id', $iikoMenuItemGroupId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

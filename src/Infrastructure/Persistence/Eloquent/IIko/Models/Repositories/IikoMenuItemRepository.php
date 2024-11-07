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

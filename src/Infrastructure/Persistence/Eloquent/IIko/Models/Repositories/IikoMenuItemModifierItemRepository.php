<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemModifierItem>
 */
final class IikoMenuItemModifierItemRepository extends AbstractPersistenceRepository implements IikoMenuItemModifierItemRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemModifierGroupId, StringId $externalId): ?Item
    {
        $result = $this->findEloquentByMenuIdAndExternalId($iikoMenuItemModifierGroupId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemModifierItem::toDomainEntity($result);
    }

    public function createOrUpdate(Item $item): Item
    {
        $iikoMenuItemModifierItem = $this->findEloquentByMenuIdAndExternalId(
            $item->itemGroupId,
            $item->externalId
        ) ?? new IikoMenuItemModifierItem();

        $iikoMenuItemModifierItem->fromDomainEntity($item);
        $iikoMenuItemModifierItem->save();

        return IikoMenuItemModifierItem::toDomainEntity($iikoMenuItemModifierItem);
    }

    public function findEloquentByMenuIdAndExternalId(
        IntegerId $iikoMenuItemModifierGroupId,
        StringId $externalId,
    ): ?IikoMenuItemModifierItem {
        return $this
            ->query()
            ->where('iiko_menu_item_modifier_group_id', $iikoMenuItemModifierGroupId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

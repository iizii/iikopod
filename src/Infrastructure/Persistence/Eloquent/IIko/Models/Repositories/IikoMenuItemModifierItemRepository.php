<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Repositories\IikoMenuItemModifierItemRepositoryInterface;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemModifierItem>
 */
final class IikoMenuItemModifierItemRepository extends AbstractPersistenceRepository implements IikoMenuItemModifierItemRepositoryInterface
{
    public function findFor(ItemSize $itemSize): ItemCollection
    {
        $result = $this
            ->query()
            ->whereHas('modifierGroup', static function (Builder $builder) use ($itemSize) {
                return $builder->whereHas('itemSizes', static function (Builder $builder) use ($itemSize) {
                    return $builder->where('id', $itemSize->id->id);
                });
            })
            ->get();

        return new ItemCollection(
            $result->map(
                static fn (IikoMenuItemModifierItem $modifierItem): Item => IikoMenuItemModifierItem::toDomainEntity(
                    $modifierItem,
                ),
            ),
        );
    }

    public function findByExternalId(StringId $id, Item $item): ?Item
    {
        $result = $this
            ->query()
            ->where('external_id', $id->id)
            ->whereHas('modifierGroup', static function (Builder $builder) use ($item) {
                $builder->whereHas('itemSizes', static function (Builder $builder) use ($item) {
                    $builder->whereHas('menuItems', static function (Builder $builder) use ($item) {
                        $builder->where('iiko_menu_items.id', $item->id->id);
                    });
                });
            })
            ->first();

        if (! $result) {
            return null;
        }

        return IikoMenuItemModifierItem::toDomainEntity($result);
    }

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
            $item->externalId,
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

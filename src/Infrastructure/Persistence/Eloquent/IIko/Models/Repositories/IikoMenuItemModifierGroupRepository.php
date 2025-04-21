<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Application\Iiko\Builders\ItemModifierGroupBuilder;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Repositories\IikoMenuItemModifierGroupRepositoryInterface;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemModifierGroup>
 */
final class IikoMenuItemModifierGroupRepository extends AbstractPersistenceRepository implements IikoMenuItemModifierGroupRepositoryInterface
{
    public function findFor(ItemSize $itemSize): ItemModifierGroupCollection
    {
        $result = $this
            ->query()
            ->where('iiko_menu_item_size_id', $itemSize->id->id)
            ->get();

        return new ItemModifierGroupCollection(
            $result->map(
                static fn (IikoMenuItemModifierGroup $iikoMenuItemModifierGroup,
                ): ItemModifierGroup => IikoMenuItemModifierGroup::toDomainEntity(
                    $iikoMenuItemModifierGroup,
                ),
            ),
        );
    }

    public function findByMenuIdAndExternalId(IntegerId $iikoMenuItemSizeId, StringId $externalId): ?ItemModifierGroup
    {
        $result = $this->findEloquentByMenuIdAndExternalId($iikoMenuItemSizeId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemModifierGroup::toDomainEntity($result);
    }

    public function createOrUpdate(ItemModifierGroup $itemModifierGroup): ItemModifierGroup
    {
        $iikoMenuItemModifierGroup = $this->findEloquentByExternalId(
            $itemModifierGroup->externalId,
        ) ?? new IikoMenuItemModifierGroup();

        $iikoMenuItemModifierGroup->fromDomainEntity($itemModifierGroup);
        $iikoMenuItemModifierGroup->save();

        if (! $iikoMenuItemModifierGroup->itemSizes()->where('iiko_menu_item_sizes.id', $itemModifierGroup->itemSizeId->id)->exists()) {
            $iikoMenuItemModifierGroup->itemSizes()->attach($itemModifierGroup->itemSizeId->id);
        }

        $builded = ItemModifierGroupBuilder::fromExisted(IikoMenuItemModifierGroup::toDomainEntity($iikoMenuItemModifierGroup));
        $builded = $builded->setItemSizeId($itemModifierGroup->itemSizeId);

        return $builded->build();
    }

    private function findEloquentByMenuIdAndExternalId(
        IntegerId $iikoMenuItemSizeId,
        StringId $externalId,
    ): ?IikoMenuItemModifierGroup {
        return $this
            ->query()
            ->where('iiko_menu_item_size_id', $iikoMenuItemSizeId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }

    public function findEloquentByExternalId(StringId $externalId): ?IikoMenuItemModifierGroup
    {
        return $this
            ->query()
            ->where('external_id', $externalId->id)
            ->first();
    }
}

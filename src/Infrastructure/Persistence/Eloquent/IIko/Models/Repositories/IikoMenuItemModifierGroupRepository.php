<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Repositories\IikoMenuItemModifierGroupRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemModifierGroup>
 */
final class IikoMenuItemModifierGroupRepository extends AbstractPersistenceRepository implements IikoMenuItemModifierGroupRepositoryInterface
{
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
        $iikoMenuItemModifierGroup = $this->findEloquentByMenuIdAndExternalId(
            $itemModifierGroup->itemSizeId,
            $itemModifierGroup->externalId
        ) ?? new IikoMenuItemModifierGroup();

        $iikoMenuItemModifierGroup->fromDomainEntity($itemModifierGroup);
        $iikoMenuItemModifierGroup->save();

        return IikoMenuItemModifierGroup::toDomainEntity($iikoMenuItemModifierGroup);
    }

    private function findEloquentByMenuIdAndExternalId(IntegerId $iikoMenuItemSizeId, StringId $externalId): ?IikoMenuItemModifierGroup
    {
        return $this
            ->query()
            ->where('iiko_menu_item_size_id', $iikoMenuItemSizeId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\Iiko\Repositories\IikoMenuItemGroupRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemGroup>
 */
final class IikoMenuItemGroupRepository extends AbstractPersistenceRepository implements IikoMenuItemGroupRepositoryInterface
{
    public function findByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?ItemGroup
    {
        $result = $this->findEloquentByMenuIdAndExternalId($iikoMenuId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemGroup::toDomainEntity($result);
    }

    public function createOrUpdate(ItemGroup $itemGroup): ItemGroup
    {
        $iikoMenuItemGroup = $this->findEloquentByMenuIdAndExternalId(
            $itemGroup->iikoMenuId,
            $itemGroup->externalId
        ) ?? new IikoMenuItemGroup();

        $iikoMenuItemGroup->fromDomainEntity($itemGroup);
        $iikoMenuItemGroup->save();

        return IikoMenuItemGroup::toDomainEntity($iikoMenuItemGroup);
    }

    public function findEloquentByMenuIdAndExternalId(IntegerId $iikoMenuId, StringId $externalId): ?IikoMenuItemGroup
    {
        return $this
            ->query()
            ->where('iiko_menu_id', $iikoMenuId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

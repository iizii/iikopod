<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemSize>
 */
final class IikoMenuItemSizeRepository extends AbstractPersistenceRepository implements IikoMenuItemSizeRepositoryInterface
{
    public function findByExternalId(IntegerId $iikoMenuItemId, StringId $externalId): ?ItemSize
    {
        $result = $this->findEloquentByExternalId($iikoMenuItemId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemSize::toDomainEntity($result);
    }

    public function createOrUpdate(ItemSize $itemSize): ItemSize
    {
        $iikoItemSize = $this->findEloquentByExternalId(
            $itemSize->itemId,
            $itemSize->externalId,
        ) ?? new IikoMenuItemSize();

        $iikoItemSize->fromDomainEntity($itemSize);
        $iikoItemSize->save();

        return IikoMenuItemSize::toDomainEntity($iikoItemSize);
    }

    private function findEloquentByExternalId(IntegerId $iikoMenuItemId, StringId $externalId): ?IikoMenuItemSize
    {
        return $this
            ->query()
            ->where('iiko_menu_item_id', $iikoMenuItemId->id)
            ->where('external_id', $externalId->id)
            ->first();
    }
}

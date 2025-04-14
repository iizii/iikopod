<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\Repositories\IikoMenuRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenu>
 */
final class IikoMenuRepository extends AbstractPersistenceRepository implements IikoMenuRepositoryInterface
{
    public function findForItem(Item $item): ?Menu
    {
        $result = $this
            ->query()
            ->whereHas(
                'itemGroups',
                static function (Builder $builder) use ($item): Builder {
                    return $builder->whereHas('items', static function (Builder $builder) use ($item): Builder {
                        return $builder->where('id', $item->id->id);
                    });
                },
            )
            ->first();

        if (! $result) {
            return null;
        }

        return IikoMenu::toDomainEntity($result);
    }

    /**
     * @return null|Collection<array-key, IikoMenu>
     */
    public function getAllByInternalOrganizationIdWithItemGroups(IntegerId $organizationId): ?Collection
    {
        /** @var Collection<array-key, IikoMenu> */
        return $this
            ->query()
            ->select(['id', 'organization_setting_id']) // Только нужные колонки
            ->where('organization_setting_id', $organizationId->id)
            ->with(['itemGroups:id,iiko_menu_id,name,is_hidden,external_id']) // Жадная загрузка с нужными колонками
            ->get()
            ->whenEmpty(static fn () => null);
    }

    public function findByExternalId(StringId $externalId): ?Menu
    {
        $result = $this->findEloquentByExternalId($externalId);

        if (! $result) {
            return null;
        }

        return IikoMenu::toDomainEntity($result);
    }

    public function createOrUpdate(Menu $menu): Menu
    {
        $iikoMenu = $this->findEloquentByExternalId($menu->externalId) ?? new IikoMenu();

        $iikoMenu = $iikoMenu->fromDomainEntity($menu);
        $iikoMenu->save();

        return IikoMenu::toDomainEntity($iikoMenu);
    }

    private function findEloquentByExternalId(StringId $externalId): ?IikoMenu
    {
        return $this
            ->query()
            ->where('external_id', $externalId->id)
            ->first();
    }
}

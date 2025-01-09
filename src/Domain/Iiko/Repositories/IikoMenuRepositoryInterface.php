<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\Menu;
use Illuminate\Database\Eloquent\Collection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuRepositoryInterface
{
    public function findForItem(Item $item): ?Menu;

    public function findByExternalId(StringId $externalId): ?Menu;

    public function createOrUpdate(Menu $menu): Menu;

    /**
     * @return null|Collection<array-key, IikoMenu>
     */
    public function getAllByInternalOrganizationIdWithItemGroups(IntegerId $organizationId): ?Collection;
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\Menu;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuRepositoryInterface
{
    public function findForItem(Item $item): ?Menu;

    public function findByExternalId(StringId $externalId): ?Menu;

    public function createOrUpdate(Menu $menu): Menu;
}

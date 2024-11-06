<?php

declare(strict_types=1);

namespace Domain\Iiko\Repositories;

use Domain\Iiko\Entities\Menu\Menu;
use Shared\Domain\ValueObjects\StringId;

interface IikoMenuRepositoryInterface
{
    public function findByExternalId(StringId $externalId): ?Menu;

    public function createOrUpdate(Menu $menu): Menu;
}

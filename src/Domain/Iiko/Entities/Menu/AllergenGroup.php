<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class AllergenGroup extends DomainEntity
{
    public function __construct(
        public readonly StringId $id,
        public readonly string $code,
        public readonly string $name,
    ) {}
}

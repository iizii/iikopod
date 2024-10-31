<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\StringId;

final class Price extends DomainEntity
{
    /**
     * @param  OrganizationIdCollection<array-key, StringId>  $organizations
     */
    public function __construct(
        public readonly OrganizationIdCollection $organizations,
        public readonly ?int $price,
    ) {}
}

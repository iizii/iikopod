<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class FoodCategory extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $iikoItemGroupId,
        public readonly IntegerId $externalId,
        public readonly string $name,
    ) {}
}

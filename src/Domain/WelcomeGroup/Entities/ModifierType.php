<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Domain\WelcomeGroup\Enums\ModifierTypeBehaviour;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class ModifierType extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $externalId,
        public readonly string $name,
        public readonly ModifierTypeBehaviour $behaviour,
    ) {}
}

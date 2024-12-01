<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Modifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $internalModifierTypeId,
        public readonly IntegerId $internalIikoItemId,
        public readonly IntegerId $externalId,
        public readonly IntegerId $externalModifierTypeId,
        public readonly StringId $iikoExternalModifierId,
        public readonly string $name,
        public readonly bool $isDefault,
    ) {}
}

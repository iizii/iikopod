<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Modifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $modifierType,
        public readonly string $name,
        public readonly bool $defaultOption,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt
    ) {}
}

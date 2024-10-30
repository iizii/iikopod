<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Modifier extends DomainEntity
{
    public function __construct(
        public IntegerId $id,
        public int $modifierType,
        public string $name,
        public bool $defaultOption,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $updatedAt
    ) {}
}

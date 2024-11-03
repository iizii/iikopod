<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use DateTimeInterface;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Phone extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly string $phone,
        public readonly DateTimeInterface $created,
        public readonly DateTimeInterface $updated,
    ) {}
}

<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use DateTimeInterface;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Address extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly string $street,
        public readonly string $house,
        public readonly DateTimeInterface $created,
        public readonly DateTimeInterface $updated,
        public readonly ?string $building = null,
        public readonly ?string $floor = null,
        public readonly ?string $flat = null,
        public readonly ?string $entry = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $comment = null,
    ) {}
}

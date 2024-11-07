<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use DateTimeInterface;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class RestaurantModifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $restaurantId,
        public readonly IntegerId $modifierId,
        public readonly ?string $statusComment,
        public readonly string $status,
        public readonly DateTimeInterface $created,
        public readonly DateTimeInterface $updated,
    ) {}
}

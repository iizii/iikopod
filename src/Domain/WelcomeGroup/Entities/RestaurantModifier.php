<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class RestaurantModifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $restaurantId,
        public readonly IntegerId $modifierId,
        public readonly IntegerId $externalId,
        public readonly IntegerId $welcomeGroupRestaurantId,
        public readonly IntegerId $welcomeGroupModifierId,
        public readonly ?string $statusComment,
        public readonly string $status,
    ) {}
}

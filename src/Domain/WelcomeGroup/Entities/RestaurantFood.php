<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class RestaurantFood extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $restaurantId,
        public readonly IntegerId $foodId,
        public readonly IntegerId $externalId,
        public readonly IntegerId $welcomeGroupRestaurantId,
        public readonly IntegerId $welcomeGroupFoodId,
        public readonly ?string $statusComment,
        public readonly string $status,
    ) {}
}

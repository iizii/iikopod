<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\RestaurantModifier;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupRestaurantModifierRepositoryInterface
{
    public function save(RestaurantModifier $restaurantModifier): RestaurantModifier;

    public function update(RestaurantModifier $restaurantModifier): RestaurantModifier;

    public function findById(IntegerId $integerId): ?RestaurantModifier;

    public function findByInternalRestaurantAndModifierId(IntegerId $internalRestaurantId, IntegerId $internalModifierId): ?RestaurantModifier;

    public function deleteByInternalId(IntegerId $id): bool;
}

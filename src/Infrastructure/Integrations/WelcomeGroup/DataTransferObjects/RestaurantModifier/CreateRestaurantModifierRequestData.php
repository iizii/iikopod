<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateRestaurantModifierRequestData extends ResponseData
{
    public function __construct(
        public readonly int $restaurant,
        public readonly int $modifier,
        public readonly ?string $status = 'active',
    ) {}
}

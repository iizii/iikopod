<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantsResponse;

use Domain\WelcomeGroup\ValueObjects\Restaurant\Emergency;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetRestaurantsResponseEmergencyData extends ResponseData
{
    public function __construct(
        public readonly string $emergencyStart,
        public readonly string $emergencyEnd,
        public readonly int $productionTimeEmergency,
    ) {}

    public function toDomainEntity(): Emergency
    {
        return new Emergency(
            $this->emergencyStart,
            $this->emergencyEnd,
            $this->productionTimeEmergency,
        );
    }
}

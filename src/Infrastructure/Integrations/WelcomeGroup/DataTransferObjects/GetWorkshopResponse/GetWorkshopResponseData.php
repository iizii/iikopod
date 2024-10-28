<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetWorkshopResponse;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Workshop;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetWorkshopResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}

    public function toDomainEntity(): Workshop
    {
        return new Workshop(
            new IntegerId($this->id),
            $this->name,
            $this->created,
            $this->updated
        );
    }
}

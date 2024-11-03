<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateAddressRequestData extends ResponseData
{
    public function __construct(
        public readonly string $city,
        public readonly string $street,
        public readonly string $house,
        public readonly ?string $building = null,
        public readonly ?string $floor = null,
        public readonly ?string $flat = null,
        public readonly ?string $entry = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $comment = null
    ) {}
}

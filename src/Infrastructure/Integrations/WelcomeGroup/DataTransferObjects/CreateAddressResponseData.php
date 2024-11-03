<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Address;
use Domain\WelcomeGroup\Entities\Client;
use Domain\WelcomeGroup\Entities\Phone;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class CreateAddressResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $city,
        public readonly string $street,
        public readonly string $house,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
        public readonly ?string $building = null,
        public readonly ?string $floor = null,
        public readonly ?string $flat = null,
        public readonly ?string $entry = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $comment = null,
    ) {}

    public function toDomainEntity(): Address
    {
        return new Address(
            new IntegerId($this->id),
            $this->street,
            $this->house,
            $this->created,
            $this->updated,
            $this->building,
            $this->floor,
            $this->flat,
            $this->entry,
            $this->latitude,
            $this->longitude,
            $this->comment
        );
    }
}

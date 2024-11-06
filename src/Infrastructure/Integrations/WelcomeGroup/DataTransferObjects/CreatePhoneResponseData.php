<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Phone;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class CreatePhoneResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $number,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}

    public function toDomainEntity(): Phone
    {
        return new Phone(
            new IntegerId($this->id),
            $this->number,
            $this->created,
            $this->updated
        );
    }
}

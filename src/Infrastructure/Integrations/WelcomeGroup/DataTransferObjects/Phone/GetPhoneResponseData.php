<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone;

use Carbon\CarbonImmutable;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class GetPhoneResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $number,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}
}

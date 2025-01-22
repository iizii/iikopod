<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Client;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class GetClientResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $status,
        public readonly ?string $comment,
        public readonly bool $blacklist,
        public readonly bool $patron,
        public readonly bool $vip,
        public readonly int $averageOrderSum,
        public readonly int $orderCount,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
        public readonly ?string $statusComment
    ) {}

    public function toDomainEntity(): Client
    {
        return new Client(
            new IntegerId($this->id),
            $this->name,
            $this->status,
            $this->comment,
            $this->blacklist,
            $this->patron,
            $this->vip,
            $this->averageOrderSum,
            $this->orderCount,
            $this->created,
            $this->updated,
            $this->statusComment
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Food;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class EditFoodResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly int $foodCategory,
        public readonly int $workshop,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}

    public function toDomainEntity(): Food
    {
        return new Food(
            new IntegerId(),
            new IntegerId(),
            new IntegerId(),
            new IntegerId($this->id),
            new IntegerId($this->foodCategory),
            new IntegerId($this->workshop),
            $this->name,
            $this->description,
            $this->weight,
            $this->caloricity,
            (int)($this->price * 100),
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class GetFoodCategoryResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}

    public function toDomainEntity(): FoodCategory
    {
        return new FoodCategory(
            new IntegerId($this->id),
            $this->name,
            $this->created,
            $this->updated
        );
    }
}

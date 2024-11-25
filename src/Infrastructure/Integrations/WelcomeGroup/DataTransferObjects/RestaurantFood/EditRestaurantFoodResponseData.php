<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\RestaurantFood;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class EditRestaurantFoodResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id,
        public readonly int $restaurant,
        public readonly int $food,
        public readonly ?string $statusComment,
        public readonly string $status,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}

    public function toDomainEntity(): RestaurantFood
    {
        return new RestaurantFood(
            new IntegerId($this->id),
            new IntegerId($this->restaurant),
            new IntegerId($this->food),
            new IntegerId(),
            new IntegerId(),
            new IntegerId(),
            $this->statusComment,
            $this->status,
        );
    }
}

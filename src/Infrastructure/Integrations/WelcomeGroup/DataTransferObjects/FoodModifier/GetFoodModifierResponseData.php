<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\FoodModifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class GetFoodModifierResponseData extends ResponseData
{
    public function __construct(
        public readonly int $id, //id
        public readonly int $food, //id
        public readonly int $modifier, //id
        public readonly int $weight,
        public readonly int $caloricity,
        public readonly float $price,
        public readonly int $duration,
        public readonly ModifierObject $modifierObject,
        public readonly string $status,
        public readonly string $statusComment,
        public readonly string $foodName,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
    ) {}

    public function toDomainEntity(): FoodModifier
    {
        return new FoodModifier(
            new IntegerId($this->id),
            new IntegerId($this->food),
            new IntegerId($this->modifier),
            $this->status,
            $this->statusComment,
            $this->weight,
            $this->caloricity,
            $this->price,
            $this->duration,
            $this->modifierObject,
            $this->created,
            $this->updated

        );
    }
}

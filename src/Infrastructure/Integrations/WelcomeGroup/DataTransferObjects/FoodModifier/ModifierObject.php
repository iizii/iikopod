<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Modifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class ModifierObject extends ResponseData
{
    public function __construct(
        public readonly int $id, //id
        public readonly int $modifierType, //id
        public readonly string $name,
        public readonly bool $defaultOption,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,

    ) {}

    public function toDomainEntity(): Modifier
    {
        return new Modifier(
            new IntegerId(),
            new IntegerId(),
            new IntegerId(),
            new IntegerId($this->id),
            new IntegerId($this->modifierType),
            new StringId(),
            $this->name,
            $this->defaultOption,
        );
    }
}

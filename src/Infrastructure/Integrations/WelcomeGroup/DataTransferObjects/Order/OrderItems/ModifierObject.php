<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class ModifierObject
{
    public function __construct(
        public readonly int $modifierType,
        public readonly string $name,
        public readonly bool $defaultOption,
        public readonly int $id,
        public readonly string $created,
        public readonly string $updated
    ) {}
}

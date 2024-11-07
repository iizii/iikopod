<?php

declare(strict_types=1);

namespace Domain\Settings\ValueObjects;

use Shared\Domain\ValueObject;
use Shared\Domain\ValueObjects\StringId;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class PriceCategory extends ValueObject
{
    public function __construct(
        public readonly StringId $categoryId,
        public readonly string $prefix,
    ) {}
}

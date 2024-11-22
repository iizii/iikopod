<?php

declare(strict_types=1);

namespace Shared\Domain\DataCasts;

use Shared\Domain\ValueObjects\StringId;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class StringIdCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): StringId
    {
        return new StringId((string) $value);
    }
}

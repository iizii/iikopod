<?php

declare(strict_types=1);

namespace Shared\Domain\DataCasts;

use Shared\Domain\ValueObjects\IntegerId;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class IntegerIdCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): IntegerId
    {
        return new IntegerId((int) $value);
    }
}

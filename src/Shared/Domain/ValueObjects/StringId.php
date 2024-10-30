<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObjects;

use Shared\Domain\ValueObject;

final class StringId extends ValueObject
{
    public function __construct(public ?string $id = null) {}
}

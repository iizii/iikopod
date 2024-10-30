<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObjects;

use Shared\Domain\ValueObject;

final class IntegerId extends ValueObject
{
    public function __construct(public readonly ?int $id = null) {}
}

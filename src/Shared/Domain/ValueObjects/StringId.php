<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObjects;

final class StringId
{
    public function __construct(public ?string $id = null) {}
}

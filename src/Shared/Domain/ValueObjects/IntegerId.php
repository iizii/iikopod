<?php

declare(strict_types=1);

namespace Shared\Domain\ValueObjects;

final readonly class IntegerId
{
    public function __construct(public ?int $id = null) {}
}

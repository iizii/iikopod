<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects\Restaurant;

use Shared\Domain\ValueObject;

final class PrinterPos extends ValueObject
{
    public function __construct(public readonly ?string $host, public readonly ?string $uri) {}
}

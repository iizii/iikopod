<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateModifierRequestData extends ResponseData
{
    public function __construct(
        public readonly string $name,
        public readonly int $modifierType,
        public readonly bool $defaultOption,
    ) {}
}

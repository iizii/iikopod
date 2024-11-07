<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateModifierTypeRequestData extends ResponseData
{
    public function __construct(
        public readonly string $name,
        public readonly string $behaviour,
    ) {}
}

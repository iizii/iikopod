<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateClientRequestData extends ResponseData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $status = 'active',
    ) {}
}

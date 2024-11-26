<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client;

use Shared\Infrastructure\Integrations\ResponseData;

final class FindClientRequestData extends ResponseData
{
    public function __construct(
        public readonly string $name,
    ) {}
}

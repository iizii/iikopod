<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetClientRequestData extends ResponseData
{
    public function __construct(
        public readonly int $id,
    ) {}
}

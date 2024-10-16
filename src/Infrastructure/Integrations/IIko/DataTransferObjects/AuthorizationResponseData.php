<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Shared\Integrations\ResponseData;

final class AuthorizationResponseData extends ResponseData
{
    public function __construct(public readonly string $apiLogin) {}
}

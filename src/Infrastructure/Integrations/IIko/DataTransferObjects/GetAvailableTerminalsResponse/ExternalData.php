<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class ExternalData extends ResponseData
{
    public function __construct(public readonly string $key, public readonly string $value) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class Items extends ResponseData
{
    /**
     * @param  ExternalData[]  $externalData
     */
    public function __construct(
        public readonly string $id,
        public readonly string $organizationId,
        public readonly string $name,
        public readonly string $address,
        public readonly string $timeZone,
        public readonly array $externalData
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class TerminalGroupData extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $organizationId,
        public readonly string $name,
        public readonly string $address,
        public readonly string $timeZone,
    ) {}
}

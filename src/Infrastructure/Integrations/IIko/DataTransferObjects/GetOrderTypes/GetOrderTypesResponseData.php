<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetOrderTypes;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetOrderTypesResponseData extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $orderServiceType,
        public readonly bool $isDeleted,
        public readonly int $externalRevision,
        public readonly bool $isDefault
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetStopListResponseData extends ResponseData
{
    public function __construct(
        public readonly int $balance,
        public readonly string $productId,
        public readonly ?string $sizeId,
        public readonly ?string $sku,
        public readonly ?string $dateAdd
    ) {}
}

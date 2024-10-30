<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class Restriction extends ResponseData
{
    public function __construct(
        public readonly int $minQuantity,
        public readonly int $maxQuantity,
        public readonly int $freeQuantity,
        public readonly int $byDefault,
        public readonly bool $hideIfDefaultQuantity
    ) {}
}

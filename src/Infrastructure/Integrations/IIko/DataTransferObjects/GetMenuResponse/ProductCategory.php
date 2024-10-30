<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class ProductCategory extends ResponseData
{
    public function __construct(
        public readonly string $name,
        public readonly string $id,
        public readonly bool $deleted,
        public readonly ?int $vatPercent
    ) {}
}

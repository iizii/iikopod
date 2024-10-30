<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class ExternalMenuData extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}
}

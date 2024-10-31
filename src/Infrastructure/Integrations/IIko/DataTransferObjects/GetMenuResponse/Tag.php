<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class Tag extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}
}

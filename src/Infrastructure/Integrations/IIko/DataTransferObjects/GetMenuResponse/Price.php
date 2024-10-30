<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class Price extends ResponseData
{
    /**
     * @param  array<array-key, string>  $organizations
     */
    public function __construct(
        public readonly array $organizations,
        public readonly ?int $price,
    ) {}
}

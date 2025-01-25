<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetOrderTypes;

use Spatie\LaravelData\Data;

final class GetOrderTypesRequestData extends Data
{
    /**
     * @param  array<array-key, string>  $organizationIds
     */
    public function __construct(
        public readonly array $organizationIds,
    ) {}
}

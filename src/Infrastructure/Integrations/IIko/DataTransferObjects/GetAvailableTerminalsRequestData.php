<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Spatie\LaravelData\Data;

final class GetAvailableTerminalsRequestData extends Data
{
    /**
     * @param  array<array-key, string>  $organizationIds
     */
    public function __construct(
        public readonly array $organizationIds,
        public readonly bool $includeDisabled = false,
        public readonly ?array $returnExternalData = ['string'],
    ) {}
}

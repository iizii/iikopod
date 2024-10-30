<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Spatie\LaravelData\Data;

final class GetOrganizationRequestData extends Data
{
    /**
     * @param  array<array-key, string>  $organizationIds
     * @param  array<array-key, string>|null  $returnExternalData
     */
    public function __construct(
        public readonly array $organizationIds,
        public readonly bool $returnAdditionalInfo,
        public readonly bool $includeDisabled,
        public readonly ?array $returnExternalData = null,
    ) {}
}

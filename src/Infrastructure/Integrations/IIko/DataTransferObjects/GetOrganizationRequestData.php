<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Spatie\LaravelData\Data;

final class GetOrganizationRequestData extends Data
{
    /**
     * @param  array<non-empty-string>  $organizationIds
     * @param  array<non-empty-string>|null  $returnExternalData
     */
    public function __construct(
        public array $organizationIds,
        public bool $returnAdditionalInfo,
        public bool $includeDisabled,
        public ?array $returnExternalData = null,
    ) {}
}

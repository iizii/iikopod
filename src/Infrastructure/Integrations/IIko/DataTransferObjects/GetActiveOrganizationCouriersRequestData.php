<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Spatie\LaravelData\Data;

final class GetActiveOrganizationCouriersRequestData extends Data
{
    /**
     * @param  array<array-key, string>  $organizationIds
     */
    public function __construct(
        public readonly array $organizationIds,
    ) {}
}

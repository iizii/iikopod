<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use InvalidArgumentException;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetMenuRequestData extends ResponseData
{
    /**
     * @param  array<array-key, string>  $organizationIds
     */
    public function __construct(
        public readonly array $organizationIds,
        public readonly string $externalMenuId,
        public readonly string $priceCategoryId,
        public readonly string $language = 'string',
        public readonly int $startRevision = 0,
    ) {
        if (empty($this->organizationIds)) {
            throw new InvalidArgumentException('Организация должна быть указана');
        }
    }
}

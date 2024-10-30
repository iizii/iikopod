<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use InvalidArgumentException;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetExternalMenusWithPriceCategoriesRequestData extends ResponseData
{
    /**
     * @param  array<array-key, string>  $organizationIds
     */
    public function __construct(
        public readonly array $organizationIds,
        public readonly string $externalMenuId = 'string',
        public readonly string $priceCategoryId = 'string',
        public readonly int $version = 0,
        public readonly string $language = 'string',
        public readonly bool $asyncMode = false,
        public readonly int $startRevision = 0,
    ) {
        if (empty($this->organizationIds)) {
            throw new InvalidArgumentException(
                'Массив идентификаторов организаций должен содержать хотя бы один элемент',
            );
        }
    }
}

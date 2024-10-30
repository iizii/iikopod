<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use InvalidArgumentException;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetExternalMenusWithPriceCategoriesRequestData extends ResponseData
{
    /**
     * @param array $organizationIds
     * @param string $externalMenuId
     * @param string $priceCategoryId
     * @param int $version
     * @param string $language
     * @param bool $asyncMode
     * @param int $startRevision
     */
    public function __construct(
        public array $organizationIds,
        public string $externalMenuId = 'string',
        public string $priceCategoryId = 'string',
        public int $version = 0,
        public string $language = 'string',
        public bool $asyncMode = false,
        public int $startRevision = 0,
    ) {
        if (empty($this->organizationIds)) {
            throw new InvalidArgumentException('Массив идентификаторов организаций должен содержать хотя бы один элемент');
        }
    }
}

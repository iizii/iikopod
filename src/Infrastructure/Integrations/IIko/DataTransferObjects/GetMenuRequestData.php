<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use InvalidArgumentException;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetMenuRequestData extends ResponseData
{
    /**
     * @param array $organizationIds
     * @param string $externalMenuId
     * @param string $priceCategoryId
     * @param int $version
     * @param string $language
     * @param int $startRevision
     */
    public function __construct(
        public array $organizationIds,
        public string $externalMenuId,
        public string $priceCategoryId,
        public int $version = 0,
        public string $language = 'string',
        public int $startRevision = 0,
    ) {
        if (empty($this->organizationIds)) {
            throw new InvalidArgumentException('Организация должна быть указана');
        }
    }
}

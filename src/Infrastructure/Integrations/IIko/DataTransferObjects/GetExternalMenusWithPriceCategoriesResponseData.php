<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetExternalMenusWithPriceCategoriesResponseData extends ResponseData
{
    /**
     * @param  ExternalMenuData[]  $externalMenus
     * @param  PriceCategoryData[]  $priceCategories
     */
    public function __construct(
        public array $externalMenus,
        public array $priceCategories
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class GetExternalMenusWithPriceCategoriesResponseData extends ResponseData
{
    /**
     * @param  DataCollection<array-key, ExternalMenuData>  $externalMenus
     * @param  DataCollection<array-key, PriceCategoryData>  $priceCategories
     */
    public function __construct(
        #[DataCollectionOf(ExternalMenuData::class)]
        public readonly DataCollection $externalMenus,
        #[DataCollectionOf(PriceCategoryData::class)]
        public readonly DataCollection $priceCategories,
    ) {}
}

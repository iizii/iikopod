<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class GetMenuResponseData extends ResponseData
{
    /**
     * @param  DataCollection<array-key, ProductCategory>  $productCategories
     * @param  DataCollection<array-key, PureExternalMenuItemCategory>  $pureExternalMenuItemCategories
     */
    public function __construct(
        #[DataCollectionOf(ProductCategory::class)]
        public readonly DataCollection $productCategories,
        public readonly int $revision,
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $buttonImageUrl,
        #[DataCollectionOf(PureExternalMenuItemCategory::class)]
        public readonly DataCollection $pureExternalMenuItemCategories
    ) {}
}

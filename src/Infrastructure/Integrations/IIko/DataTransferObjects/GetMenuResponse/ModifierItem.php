<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class ModifierItem extends ResponseData
{
    /**
     * @param  DataCollection<array-key, AllergenGroup>  $allergenGroups
     * @param  DataCollection<array-key, Tag>  $tags
     * @param  DataCollection<array-key, Price>  $prices
     */
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly Restriction $restrictions,
        #[DataCollectionOf(AllergenGroup::class)]
        public readonly DataCollection $allergenGroups,
        public readonly Nutrition $nutritionPerHundredGrams,
        public readonly int $portionWeightGrams,
        #[DataCollectionOf(Tag::class)]
        public readonly DataCollection $tags,
        public readonly string $iikoItemId,
        public readonly bool $hidden,
        #[DataCollectionOf(Price::class)]
        public readonly DataCollection $prices,
        public readonly int $position,
        public readonly ?string $taxCategoryId,
        public readonly bool $independentQuantity,
        public readonly ?string $productCategoryId,
        public readonly ?string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly string $measureUnitType,
        public readonly ?string $buttonImageUrl,
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class ItemSize extends ResponseData
{
    /**
     * @param  DataCollection<array-key, ItemModifierGroup>  $itemModifierGroups
     * @param  DataCollection<array-key, Price>  $prices
     * @param  DataCollection<array-key, Nutrition>  $nutritions
     */
    public function __construct(
        public readonly string $sku,
        public readonly string $sizeCode,
        public readonly string $sizeName,
        public readonly bool $isDefault,
        public readonly int $portionWeightGrams,
        #[DataCollectionOf(ItemModifierGroup::class)]
        public readonly DataCollection $itemModifierGroups,
        public readonly ?string $iikoSizeId,
        public readonly Nutrition $nutritionPerHundredGrams,
        #[DataCollectionOf(Price::class)]
        public readonly DataCollection $prices,
        #[DataCollectionOf(Nutrition::class)]
        public readonly DataCollection $nutritions,
        public readonly string $measureUnitType,
        public readonly ?string $buttonImageCroppedUrl,
        public readonly ?string $buttonImageUrl,
    ) {}
}

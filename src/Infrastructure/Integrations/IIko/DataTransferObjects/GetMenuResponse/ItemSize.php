<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\ItemModifierGroup as DomainItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize as DomainItemSize;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Iiko\ValueObjects\Menu\Nutrition as DomainNutrition;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Iiko\ValueObjects\Menu\Price as DomainPrice;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\ValueObjects\StringId;
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

    public function toDomainEntity(): DomainItemSize
    {
        return new DomainItemSize(
            new StringId($this->iikoSizeId),
            $this->sku,
            $this->sizeCode,
            $this->sizeName,
            $this->isDefault,
            $this->portionWeightGrams,
            $this->nutritionPerHundredGrams->toDomainEntity(),
            $this->measureUnitType,
            $this->buttonImageCroppedUrl,
            $this->buttonImageUrl,
            new ItemModifierGroupCollection(
                $this
                    ->itemModifierGroups
                    ->toCollection()
                    ->map(static fn (ItemModifierGroup $itemModifierGroup): DomainItemModifierGroup => $itemModifierGroup->toDomainEntity())
            ),
            new PriceCollection(
                $this
                    ->prices
                    ->toCollection()
                    ->map(static fn (Price $price): DomainPrice => $price->toDomainEntity()),
            ),
            new NutritionCollection(
                $this
                    ->nutritions
                    ->toCollection()
                    ->map(static fn (Nutrition $nutrition): DomainNutrition => $nutrition->toDomainEntity())
            ),
        );
    }
}

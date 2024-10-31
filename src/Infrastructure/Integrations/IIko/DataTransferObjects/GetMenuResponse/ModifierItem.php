<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\AllergenGroup as DomainAllergenGroup;
use Domain\Iiko\Entities\Menu\ModifierItem as DomainModifierItem;
use Domain\Iiko\Entities\Menu\Tag as DomainTag;
use Domain\Iiko\ValueObjects\Menu\AllergenGroupCollection;
use Domain\Iiko\ValueObjects\Menu\Price as DomainPrice;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Domain\Iiko\ValueObjects\Menu\TagCollection;
use Shared\Domain\ValueObjects\StringId;
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
        public readonly Nutrition $nutritionPerHundredGrams,
        public readonly int $portionWeightGrams,
        public readonly string $iikoItemId,
        public readonly bool $hidden,
        public readonly int $position,
        public readonly ?string $taxCategoryId,
        public readonly bool $independentQuantity,
        public readonly ?string $productCategoryId,
        public readonly ?string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly string $measureUnitType,
        public readonly ?string $buttonImageUrl,
        #[DataCollectionOf(Price::class)]
        public readonly DataCollection $prices,
        #[DataCollectionOf(Tag::class)]
        public readonly DataCollection $tags,
        #[DataCollectionOf(AllergenGroup::class)]
        public readonly DataCollection $allergenGroups,
    ) {}

    public function toDomainEntity(): DomainModifierItem
    {
        return new DomainModifierItem(
            new StringId($this->iikoItemId),
            new StringId($this->taxCategoryId),
            new StringId($this->productCategoryId),
            $this->sku,
            $this->name,
            $this->description,
            $this->restrictions->toDomainEntity(),
            $this->nutritionPerHundredGrams->toDomainEntity(),
            $this->portionWeightGrams,
            $this->hidden,
            $this->position,
            $this->independentQuantity,
            $this->paymentSubject,
            $this->outerEanCode,
            $this->measureUnitType,
            $this->buttonImageUrl,
            new AllergenGroupCollection(
                $this
                    ->allergenGroups
                    ->toCollection()
                    ->map(
                        static fn (AllergenGroup $allergenGroup): DomainAllergenGroup => $allergenGroup->toDomainEntity(
                        ),
                    ),
            ),
            new TagCollection(
                $this
                    ->tags
                    ->toCollection()
                    ->map(static fn (Tag $tag): DomainTag => $tag->toDomainEntity()),
            ),
            new PriceCollection(
                $this
                    ->prices
                    ->toCollection()
                    ->map(static fn (Price $price): DomainPrice => $price->toDomainEntity()),
            ),
        );
    }
}

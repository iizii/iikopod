<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\AllergenGroup as DomainAllergenGroup;
use Domain\Iiko\Entities\Menu\Item as DomainItem;
use Domain\Iiko\Entities\Menu\ItemSize as DomainItemSize;
use Domain\Iiko\Entities\Menu\Tag as DomainTag;
use Domain\Iiko\ValueObjects\Menu\AllergenGroupCollection;
use Domain\Iiko\ValueObjects\Menu\AllergenGroupIdCollection;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\TagCollection;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class Item extends ResponseData
{
    /**
     * @param  DataCollection<array-key, AllergenGroup>  $allergenGroups
     * @param  DataCollection<array-key, Tag>  $tags
     * @param  DataCollection<array-key, ItemSize>  $itemSizes
     * @param  array<array-key, string>  $allergenGroupIds
     */
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly string $iikoItemId,
        public readonly ?string $iikoModifierSchemaId,
        public readonly ?string $taxCategory,
        public readonly string $iikoModifierSchemaName,
        public readonly string $type,
        public readonly bool $canBeDivided,
        public readonly bool $canSetOpenPrice,
        public readonly bool $useBalanceForSell,
        public readonly string $measureUnit,
        public readonly ?string $productCategoryId,
        public readonly string $paymentSubject,
        public readonly ?string $outerEanCode,
        public readonly bool $isHidden,
        public readonly string $orderItemType,
        public readonly ?string $taxCategoryId,
        public readonly array $allergenGroupIds,
        #[DataCollectionOf(AllergenGroup::class)]
        public readonly DataCollection $allergenGroups,
        #[DataCollectionOf(Tag::class)]
        public readonly DataCollection $tags,
        #[DataCollectionOf(ItemSize::class)]
        public readonly DataCollection $itemSizes,
    ) {}

    public function toDomainEntity(): DomainItem
    {
        $allergenGroupIds = new AllergenGroupIdCollection();

        foreach ($this->allergenGroupIds as $allergenGroupId) {
            $allergenGroupIds->add(new StringId($allergenGroupId));
        }

        return new DomainItem(
            new StringId($this->iikoItemId),
            new StringId($this->iikoModifierSchemaId),
            new StringId($this->taxCategoryId),
            new StringId($this->productCategoryId),
            $this->sku,
            $this->name,
            $this->description,
            $this->taxCategory,
            $this->iikoModifierSchemaName,
            $this->type,
            $this->canBeDivided,
            $this->canSetOpenPrice,
            $this->useBalanceForSell,
            $this->measureUnit,
            $this->paymentSubject,
            $this->outerEanCode,
            $this->isHidden,
            $this->orderItemType,
            new AllergenGroupCollection(
                $this
                    ->allergenGroups
                    ->toCollection()
                    ->map(static fn (AllergenGroup $allergenGroup): DomainAllergenGroup => $allergenGroup->toDomainEntity()),
            ),
            new TagCollection(
                $this
                    ->tags
                    ->toCollection()
                    ->map(static fn (Tag $tag): DomainTag => $tag->toDomainEntity()),
            ),
            new ItemSizeCollection(
                $this
                    ->itemSizes
                    ->toCollection()
                    ->map(static fn (ItemSize $itemSize): DomainItemSize => $itemSize->toDomainEntity()),
            ),
            $allergenGroupIds,
        );
    }
}

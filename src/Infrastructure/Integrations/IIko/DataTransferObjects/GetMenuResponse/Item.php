<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

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
        #[DataCollectionOf(AllergenGroup::class)]
        public readonly DataCollection $allergenGroups,
        #[DataCollectionOf(Tag::class)]
        public readonly DataCollection $tags,
        #[DataCollectionOf(ItemSize::class)]
        public readonly DataCollection $itemSizes,
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
    ) {}
}

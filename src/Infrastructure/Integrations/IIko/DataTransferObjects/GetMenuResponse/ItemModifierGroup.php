<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class ItemModifierGroup extends ResponseData
{
    /**
     * @param  DataCollection<array-key, ModifierItem>  $items
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly Restriction $restrictions,
        #[DataCollectionOf(ModifierItem::class)]
        public readonly DataCollection $items,
        public readonly bool $canBeDivided,
        public readonly string $iikoItemGroupId,
        public readonly bool $hidden,
        public readonly bool $childModifiersHaveMinMaxRestrictions,
        public readonly string $sku,
    ) {}
}

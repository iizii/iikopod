<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\ItemModifierGroup as DomainItemModifierGroup;
use Domain\Iiko\Entities\Menu\ModifierItem as DomainModifierItem;
use Domain\Iiko\ValueObjects\Menu\ModifierItemCollection;
use Shared\Domain\ValueObjects\StringId;
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
        public readonly bool $canBeDivided,
        public readonly string $iikoItemGroupId,
        public readonly bool $hidden,
        public readonly bool $childModifiersHaveMinMaxRestrictions,
        public readonly string $sku,
        #[DataCollectionOf(ModifierItem::class)]
        public readonly DataCollection $items,
    ) {}

    public function toDomainEntity(): DomainItemModifierGroup
    {
        return new DomainItemModifierGroup(
            new StringId($this->iikoItemGroupId),
            $this->name,
            $this->description,
            $this->restrictions->toDomainEntity(),
            $this->canBeDivided,
            $this->hidden,
            $this->childModifiersHaveMinMaxRestrictions,
            $this->sku,
            new ModifierItemCollection(
                $this
                    ->items
                    ->toCollection()
                    ->map(static fn (ModifierItem $modifierItem): DomainModifierItem => $modifierItem->toDomainEntity()),
            ),
        );
    }
}

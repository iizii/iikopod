<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Item as DomainItem;
use Domain\Iiko\Entities\Menu\ItemModifierGroup as DomainItemModifierGroup;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class ItemModifierGroup extends ResponseData
{
    /**
     * @param  DataCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly Restriction $restrictions,
        public readonly bool $splittable,
        public readonly bool $isHidden,
        public readonly bool $childModifiersHaveMinMaxRestrictions,
        public readonly string $sku,
        #[DataCollectionOf(Item::class)]
        public readonly DataCollection $items,
    ) {}

    public function toDomainEntity(): DomainItemModifierGroup
    {
        return new DomainItemModifierGroup(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->restrictions->maxQuantity,
            $this->name,
            $this->description,
            $this->splittable,
            $this->isHidden,
            $this->childModifiersHaveMinMaxRestrictions,
            $this->sku,
            new ItemCollection(
                $this
                    ->items
                    ->toCollection()
                    ->map(static fn (Item $item): DomainItem => $item->toDomainEntity()),
            ),
        );
    }
}

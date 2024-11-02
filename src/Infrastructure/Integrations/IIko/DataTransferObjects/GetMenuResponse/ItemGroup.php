<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Item as DomainItem;
use Domain\Iiko\Entities\Menu\ItemGroup as DomainPureExternalMenuItemCategory;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class ItemGroup extends ResponseData
{
    /**
     * @param  DataCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly bool $isHidden,
        #[DataCollectionOf(Item::class)]
        public readonly DataCollection $items,
    ) {}

    public function toDomainEntity(): DomainPureExternalMenuItemCategory
    {
        return new DomainPureExternalMenuItemCategory(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->name,
            $this->description,
            $this->isHidden,
            new ItemCollection(
                $this
                    ->items
                    ->toCollection()
                    ->map(static fn (Item $item): DomainItem => $item->toDomainEntity()),
            ),
        );
    }
}

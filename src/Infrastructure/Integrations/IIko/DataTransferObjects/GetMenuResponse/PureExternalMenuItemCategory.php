<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Item as DomainItem;
use Domain\Iiko\Entities\Menu\PureExternalMenuItemCategory as DomainPureExternalMenuItemCategory;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class PureExternalMenuItemCategory extends ResponseData
{
    /**
     * @param  DataCollection<array-key, Item>  $items
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $buttonImageUrl,
        public readonly ?string $headerImageUrl,
        public readonly ?string $iikoGroupId,
        public readonly ?string $scheduleId,
        public readonly ?string $scheduleName,
        public readonly bool $isHidden,
        #[DataCollectionOf(Item::class)]
        public readonly DataCollection $items,
    ) {}

    public function toDomainEntity(): DomainPureExternalMenuItemCategory
    {
        return new DomainPureExternalMenuItemCategory(
            new StringId($this->id),
            new StringId($this->scheduleId),
            new StringId($this->iikoGroupId),
            $this->name,
            $this->description,
            $this->buttonImageUrl,
            $this->headerImageUrl,
            $this->scheduleName,
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

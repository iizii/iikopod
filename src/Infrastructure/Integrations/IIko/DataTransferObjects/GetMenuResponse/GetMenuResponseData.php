<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\ItemGroup as DomainItemGroup;
use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\ValueObjects\Menu\ItemGroupCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class GetMenuResponseData extends ResponseData
{
    /**
     * @param  DataCollection<array-key, ItemGroup>  $itemGroups
     */
    public function __construct(
        public readonly string $id,
        public readonly int $revision,
        public readonly string $name,
        public readonly string $description,
        #[DataCollectionOf(ItemGroup::class)]
        public readonly DataCollection $itemGroups,
    ) {}

    public function toDomainEntity(): Menu
    {
        return new Menu(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->revision,
            $this->name,
            $this->description,
            new ItemGroupCollection(
                $this
                    ->itemGroups
                    ->toCollection()
                    ->map(static fn (ItemGroup $itemGroup): DomainItemGroup => $itemGroup->toDomainEntity()),
            ),
        );
    }
}

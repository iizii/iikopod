<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Item as DomainItem;
use Domain\Iiko\Entities\Menu\ItemSize as DomainItemSize;
use Domain\Iiko\Entities\Menu\Price as DomainPrice;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class Item extends ResponseData
{
    /**
     * @param  ?DataCollection<array-key, ItemSize>  $itemSizes
     */
    public function __construct(
        public readonly string $id,
        public readonly string $sku,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $type,
        public readonly ?string $measureUnit,
        public readonly ?string $paymentSubject,
        public readonly bool $isHidden,
        #[DataCollectionOf(Price::class)]
        public readonly ?DataCollection $prices,
        #[DataCollectionOf(ItemSize::class)]
        public readonly ?DataCollection $itemSizes,
    ) {}

    public function toDomainEntity(): DomainItem
    {
        return new DomainItem(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->sku,
            $this->name,
            $this->description,
            $this->type,
            $this->measureUnit,
            $this->paymentSubject,
            $this->isHidden,
            new PriceCollection(
                $this
                    ->prices
                    ?->toCollection()
                    ->map(static fn (Price $price): DomainPrice => $price->toDomainEntity()),
            ),
            new ItemSizeCollection(
                $this
                    ->itemSizes
                    ?->toCollection()
                    ->map(static fn (ItemSize $itemSize): DomainItemSize => $itemSize->toDomainEntity()),
            ),
        );
    }
}

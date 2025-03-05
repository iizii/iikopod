<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\ItemModifierGroup as DomainItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize as DomainItemSize;
use Domain\Iiko\Entities\Menu\Price as DomainPrice;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class ItemSize extends ResponseData
{
    /** @var DataCollection<array-key, ItemModifierGroup> */
    public readonly DataCollection $itemModifierGroups;

    /**
     * @param  DataCollection<array-key, Price>  $prices
     * @param  DataCollection<array-key, Nutrition>  $nutritions
     */
    public function __construct(
        public readonly ?string $id,
        public readonly string $sku,
        public readonly ?bool $isDefault,
        public readonly int $weight,
        public readonly ?Nutrition $nutritionPerHundredGrams,
        public readonly string $measureUnitType,
        #[DataCollectionOf(Price::class)]
        public readonly DataCollection $prices,
        #[DataCollectionOf(Nutrition::class)]
        public readonly DataCollection $nutritions,
        array $itemModifierGroups,
    ) {
        $data = array_map(static fn (array $itemModifierGroup) => new ItemModifierGroup(
            $itemModifierGroup['id'] ?? null,
            $itemModifierGroup['name'] ?? '',
            $itemModifierGroup['description'] ?? '',
            isset($itemModifierGroup['restrictions']) ? new Restriction(
                $itemModifierGroup['restrictions']['minQuantity'] ?? 0,
                $itemModifierGroup['restrictions']['maxQuantity'] ?? 0,
                $itemModifierGroup['restrictions']['freeQuantity'] ?? 0,
                $itemModifierGroup['restrictions']['defaultQuantity'] ?? 0,
                $itemModifierGroup['restrictions']['hideIfDefaultQuantity'] ?? false,
            ) : null,
            $itemModifierGroup['splittable'] ?? false,
            $itemModifierGroup['isHidden'] ?? false,
            $itemModifierGroup['childModifiersHaveMinMaxRestrictions'] ?? false,
            $itemModifierGroup['sku'] ?? '',
            $itemModifierGroup['items'] ?? [],
        ), array_filter($itemModifierGroups, fn ($group) => isset($group['id'])));

        // Создаём DataCollection
        $this->itemModifierGroups = new DataCollection(ItemModifierGroup::class, $data);
    }

    public function toDomainEntity(): DomainItemSize
    {
        return new DomainItemSize(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->sku,
            $this->isDefault,
            $this->weight,
            $this->measureUnitType,
            new ItemModifierGroupCollection(
                $this
                    ->itemModifierGroups
                    ->toCollection()
                    ->map(static fn (ItemModifierGroup $itemModifierGroup): DomainItemModifierGroup => $itemModifierGroup->toDomainEntity())
            ),
            new PriceCollection(
                $this
                    ->prices
                    ->toCollection()
                    ->map(static fn (Price $price): DomainPrice => $price->toDomainEntity()),
            ),
            new NutritionCollection([$this->nutritionPerHundredGrams->toDomainEntity()]),
        );
    }
}

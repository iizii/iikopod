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
//    /** @var DataCollection<array-key, ItemModifierGroup> */
//    public readonly DataCollection $itemModifierGroups;

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
        #[DataCollectionOf(ItemModifierGroup::class)]
        public DataCollection $itemModifierGroups
    )
    {

        $this->itemModifierGroups = $this->itemModifierGroups
            ->toCollection()
            ->filter(static fn (ItemModifierGroup $group) => ! is_null($group->id))
            ->values(); // Убираем возможные разрывы в индексах массива

        // Приводим к нужному типу внутри конструктора
        //        $this->itemModifierGroups = new DataCollection(ItemModifierGroup::class, $filteredItems);
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

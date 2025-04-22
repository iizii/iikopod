<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Repositories;

use Application\Iiko\Builders\ItemBuilder;
use Application\Iiko\Builders\ItemModifierGroupBuilder;
use Application\Iiko\Builders\ItemSizeBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\Entities\Menu\ItemSize;
use Domain\Iiko\Entities\Menu\Nutrition;
use Domain\Iiko\Entities\Menu\Price;
use Domain\Iiko\Repositories\IikoMenuItemSizeRepositoryInterface;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Domain\Iiko\ValueObjects\Menu\ItemModifierGroupCollection;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\NutritionCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItemPrice;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Persistence\Repositories\AbstractPersistenceRepository;

/**
 * @extends AbstractPersistenceRepository<IikoMenuItemSize>
 */
final class IikoMenuItemSizeRepository extends AbstractPersistenceRepository implements IikoMenuItemSizeRepositoryInterface
{
    public function finById(IntegerId $id): ?ItemSize
    {
        $result = $this->query()->find($id);

        if (! $result) {
            return null;
        }

        return IikoMenuItemSize::toDomainEntity($result);
    }

    public function findFor(Item $item): ItemSizeCollection
    {
        $result = $this
            ->query()
            ->where('iiko_menu_item_id', $item->id->id)
            ->get();

        return new ItemSizeCollection(
            $result->map(
                static fn (IikoMenuItemSize $itemSize): ItemSize => IikoMenuItemSize::toDomainEntity($itemSize),
            ),
        );
    }

    public function findForWithAllRelations(Item $item): ItemSizeCollection
    {
        $result = $this
            ->query()
            ->with([
                'nutritions',
                'prices',
                'itemModifierGroups.items.prices',
            ])
            ->whereHas('menuItems', static function ($query) use ($item) {
                $query->where('iiko_menu_items.id', $item->id->id);
            })
            ->get();

        return new ItemSizeCollection(
            $result->map(static function (IikoMenuItemSize $itemSize): ItemSize {
                $itemSizeBuilder = ItemSizeBuilder::fromExisted(IikoMenuItemSize::toDomainEntity($itemSize));

                $nutritions = $itemSize->nutritions->map(
                    static function (IikoMenuItemNutrition $iikoMenuItemNutrition): Nutrition {
                        return IikoMenuItemNutrition::toDomainEntity($iikoMenuItemNutrition);
                    },
                );

                $prices = $itemSize->prices->map(static function (IikoMenuItemPrice $iikoMenuItemPrice): Price {
                    return IikoMenuItemPrice::toDomainEntity($iikoMenuItemPrice);
                });

                $modifiers = $itemSize->itemModifierGroups->map(
                    static function (IikoMenuItemModifierGroup $itemModifierGroup): ItemModifierGroup {
                        $itemModifierGroupBuilder = ItemModifierGroupBuilder::fromExisted(
                            IikoMenuItemModifierGroup::toDomainEntity($itemModifierGroup),
                        );

                        $items = $itemModifierGroup->items->map(
                            static function (IikoMenuItemModifierItem $iikoMenuItemModifierItem): Item {
                                $itemBuilder = ItemBuilder::fromExisted(
                                    IikoMenuItemModifierItem::toDomainEntity($iikoMenuItemModifierItem),
                                );

                                $prices = $iikoMenuItemModifierItem->prices->map(
                                    static function (IikoMenuItemModifierItemPrice $iikoMenuItemModifierItemPrice,
                                    ): Price {
                                        return IikoMenuItemModifierItemPrice::toDomainEntity(
                                            $iikoMenuItemModifierItemPrice,
                                        );
                                    },
                                );

                                return $itemBuilder
                                    ->setPrices(new PriceCollection($prices))
                                    ->build();
                            },
                        );

                        return $itemModifierGroupBuilder
                            ->setItems(new ItemCollection($items))
                            ->build();
                    },
                );

                return $itemSizeBuilder
                    ->setPrices(new PriceCollection($prices))
                    ->setNutritions(new NutritionCollection($nutritions))
                    ->setItemModifierGroups(new ItemModifierGroupCollection($modifiers))
                    ->build();
            }),
        );
    }

    public function findByExternalId(IntegerId $iikoMenuItemId, StringId $externalId): ?ItemSize
    {
        $result = $this->findEloquentByExternalId($iikoMenuItemId, $externalId);

        if (! $result) {
            return null;
        }

        return IikoMenuItemSize::toDomainEntity($result);
    }

    public function createOrUpdate(ItemSize $itemSize): ItemSize
    {
        // 1. Ищем itemSize только по external_id
        $iikoItemSize = $this->findEloquentBySku($itemSize->sku);

        // 2. Если не найден — создаём новый
        if (! $iikoItemSize) {
            $iikoItemSize = new IikoMenuItemSize();
        }

        // 3. Обновляем/заполняем данные
        $iikoItemSize->fromDomainEntity($itemSize);
        $iikoItemSize->save();

        // 4. Проверяем, есть ли связь с iikoItem
        if (! $iikoItemSize->menuItems()->where('iiko_menu_items.id', $itemSize->itemId->id)->exists()) {
            $iikoItemSize->menuItems()->attach($itemSize->itemId->id);
        }

        // 5. Возвращаем доменный объект
        $builder = ItemSizeBuilder::fromExisted(IikoMenuItemSize::toDomainEntity($iikoItemSize))
            ->setItemId($itemSize->itemId);

        return $builder->build();
    }

    /*
     * external_id для itemSize может быть null, поэтому не уверен даже в смысле этого метода
     */
    private function findEloquentByExternalId(StringId $externalId): ?IikoMenuItemSize
    {
        return $this
            ->query()
            ->where('external_id', $externalId->id)
            ->first();
    }

    private function findEloquentBySku(string $sku): ?IikoMenuItemSize
    {
        return $this
            ->query()
            ->where('sku', $sku)
            ->first();
    }
}

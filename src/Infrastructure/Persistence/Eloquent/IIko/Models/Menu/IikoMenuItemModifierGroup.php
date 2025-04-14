<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * 
 *
 * @property int $id
 * @property int $iiko_menu_item_size_id
 * @property string $external_id
 * @property int $max_quantity
 * @property string $name
 * @property string $sku
 * @property string|null $description
 * @property bool $splittable
 * @property bool $is_hidden
 * @property bool $child_modifiers_have_min_max_restrictions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize $itemSize
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereChildModifiersHaveMinMaxRestrictions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereIikoMenuItemSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereMaxQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereSplittable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class IikoMenuItemModifierGroup extends Model
{
    protected $fillable = [
        'iiko_menu_item_size_id',
        'external_id',
        'max_quantity',
        'name',
        'sku',
        'description',
        'splittable',
        'is_hidden',
        'child_modifiers_have_min_max_restrictions',
    ];

    public function itemSize(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemSize::class, 'iiko_menu_item_size_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItemModifierItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(IikoMenuItemModifierItem::class, 'iiko_menu_item_modifier_group_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'splittable' => 'boolean',
            'child_modifiers_have_min_max_restrictions' => 'boolean',
            'max_quantity' => 'integer',
        ];
    }

    public function fromDomainEntity(ItemModifierGroup $itemModifierGroup): self
    {
        return $this->fill([
            'iiko_menu_item_size_id' => $itemModifierGroup->itemSizeId->id,
            'external_id' => $itemModifierGroup->externalId->id,
            'max_quantity' => $itemModifierGroup->maxQuantity,
            'name' => $itemModifierGroup->name,
            'sku' => $itemModifierGroup->sku,
            'description' => $itemModifierGroup->description,
            'splittable' => $itemModifierGroup->splittable,
            'is_hidden' => $itemModifierGroup->isHidden,
            'child_modifiers_have_min_max_restrictions' => $itemModifierGroup->childModifiersHaveMinMaxRestrictions,
        ]);
    }

    public static function toDomainEntity(self $iikoMenuItemModifierGroup): ItemModifierGroup
    {
        return new ItemModifierGroup(
            new IntegerId($iikoMenuItemModifierGroup->id),
            new IntegerId($iikoMenuItemModifierGroup->iiko_menu_item_size_id),
            new StringId($iikoMenuItemModifierGroup->external_id),
            $iikoMenuItemModifierGroup->max_quantity,
            $iikoMenuItemModifierGroup->name,
            $iikoMenuItemModifierGroup->description,
            $iikoMenuItemModifierGroup->splittable,
            $iikoMenuItemModifierGroup->is_hidden,
            $iikoMenuItemModifierGroup->child_modifiers_have_min_max_restrictions,
            $iikoMenuItemModifierGroup->sku,
            new ItemCollection(),
        );
    }
}

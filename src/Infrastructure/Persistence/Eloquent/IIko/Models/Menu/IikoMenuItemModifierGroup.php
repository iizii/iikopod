<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $iiko_menu_item_size_id
 * @property string $external_id
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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereChildModifiersHaveMinMaxRestrictions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereIikoMenuItemSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereSplittable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemModifierGroup extends Model
{
    protected $fillable = [
        'iiko_menu_item_size_id',
        'external_id',
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
        ];
    }
}

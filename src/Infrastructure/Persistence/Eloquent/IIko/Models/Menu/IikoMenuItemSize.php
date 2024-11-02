<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $iiko_menu_item_id
 * @property string|null $external_id
 * @property string $sku
 * @property string $measure_unit_type
 * @property bool $is_default
 * @property int $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup> $itemModifierGroups
 * @property-read int|null $item_modifier_groups_count
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem $menuItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemNutrition> $nutritions
 * @property-read int|null $nutritions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemPrice> $prices
 * @property-read int|null $prices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereIikoMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereMeasureUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemSize whereWeight($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemSize extends Model
{
    protected $fillable = [
        'iiko_menu_item_id',
        'external_id',
        'sku',
        'measure_unit_type',
        'is_default',
        'weight',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItem::class, 'iiko_menu_item_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItemModifierGroup>
     */
    public function itemModifierGroups(): HasMany
    {
        return $this->hasMany(IikoMenuItemModifierGroup::class, 'iiko_menu_item_size_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItemPrice>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(IikoMenuItemPrice::class, 'iiko_menu_item_size_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItemPrice>
     */
    public function nutritions(): HasMany
    {
        return $this->hasMany(IikoMenuItemNutrition::class, 'iiko_menu_item_size_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}

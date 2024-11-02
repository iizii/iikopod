<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $iiko_menu_item_size_id
 * @property int|null $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize $itemSize
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice whereIikoMenuItemSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemPrice extends Model
{
    protected $fillable = [
        'iiko_menu_item_size_id',
        'price',
    ];

    public function itemSize(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemSize::class, 'iiko_menu_item_size_id', 'id');
    }
}

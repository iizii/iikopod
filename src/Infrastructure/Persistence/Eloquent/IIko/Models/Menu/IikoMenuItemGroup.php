<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $iiko_menu_id
 * @property string $external_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu $iikoMenu
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem> $items
 * @property-read int|null $items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereIikoMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemGroup extends Model
{
    protected $fillable = [
        'iiko_menu_id',
        'external_id',
        'name',
        'description',
        'is_hidden',
    ];

    public function iikoMenu(): BelongsTo
    {
        return $this->belongsTo(IikoMenu::class, 'iiko_menu_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(IikoMenuItem::class, 'iiko_menu_item_group_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }
}

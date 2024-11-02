<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $iiko_menu_id
 * @property string $external_id
 * @property string $name
 * @property bool $is_deleted
 * @property int|null $percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu $iikoMenu
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereIikoMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuProductCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuProductCategory extends Model
{
    protected $fillable = [
        'iiko_menu_id',
        'external_id',
        'name',
        'is_deleted',
        'percentage',
    ];

    public function iikoMenu(): BelongsTo
    {
        return $this->belongsTo(IikoMenu::class, 'iiko_menu_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
        ];
    }
}

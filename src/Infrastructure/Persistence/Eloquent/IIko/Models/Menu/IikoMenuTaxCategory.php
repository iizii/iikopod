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
 * @property int|null $percentage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu $iikoMenu
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory whereIikoMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuTaxCategory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuTaxCategory extends Model
{
    protected $fillable = [
        'iiko_menu_id',
        'external_id',
        'name',
        'percentage',
    ];

    public function iikoMenu(): BelongsTo
    {
        return $this->belongsTo(IikoMenu::class, 'iiko_menu_id', 'id');
    }
}

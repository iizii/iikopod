<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $iiko_menu_item_modifier_group_id
 * @property string $external_id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property string|null $type
 * @property string|null $measure_unit
 * @property string|null $payment_subject
 * @property bool $is_hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup $modifierGroup
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereIikoMenuItemModifierGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereMeasureUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem wherePaymentSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemModifierItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemModifierItem extends Model
{
    protected $fillable = [
        'iiko_menu_item_modifier_group_id',
        'external_id',
        'sku',
        'name',
        'description',
        'type',
        'measure_unit',
        'payment_subject',
        'is_hidden',
    ];

    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemModifierGroup::class, 'iiko_menu_item_modifier_group_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $iiko_menu_item_size_id
 * @property float $fats
 * @property float $proteins
 * @property float $carbs
 * @property float $energy
 * @property float|null $saturated_fatty_acid
 * @property float|null $salt
 * @property float|null $sugar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize $itemSize
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereCarbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereEnergy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereFats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereIikoMenuItemSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereProteins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereSalt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereSaturatedFattyAcid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereSugar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenuItemNutrition whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemNutrition extends Model
{
    protected $fillable = [
        'iiko_menu_item_size_id',
        'fats',
        'proteins',
        'carbs',
        'energy',
        'saturated_fatty_acid',
        'salt',
        'sugar',
    ];

    public function itemSize(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemSize::class, 'iiko_menu_item_size_id', 'id');
    }
}

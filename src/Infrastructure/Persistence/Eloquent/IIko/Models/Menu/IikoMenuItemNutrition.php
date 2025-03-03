<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Nutrition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * 
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereCarbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereEnergy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereFats($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereIikoMenuItemSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereProteins($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereSalt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereSaturatedFattyAcid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereSugar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemNutrition whereUpdatedAt($value)
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

    public function fromDomainEntity(Nutrition $nutrition): self
    {
        return $this->fill([
            'iiko_menu_item_size_id' => $nutrition->itemSizeId->id,
            'fats' => $nutrition->fats,
            'proteins' => $nutrition->proteins,
            'carbs' => $nutrition->carbs,
            'energy' => $nutrition->energy,
            'saturated_fatty_acid' => $nutrition->saturatedFattyAcid,
            'salt' => $nutrition->salt,
            'sugar' => $nutrition->sugar,
        ]);
    }

    public static function toDomainEntity(self $iikoMenuItemNutrition): Nutrition
    {
        return new Nutrition(
            new IntegerId($iikoMenuItemNutrition->id),
            new IntegerId($iikoMenuItemNutrition->iiko_menu_item_size_id),
            $iikoMenuItemNutrition->fats,
            $iikoMenuItemNutrition->proteins,
            $iikoMenuItemNutrition->carbs,
            $iikoMenuItemNutrition->energy,
            $iikoMenuItemNutrition->saturated_fatty_acid,
            $iikoMenuItemNutrition->salt,
            $iikoMenuItemNutrition->sugar,
        );
    }
}

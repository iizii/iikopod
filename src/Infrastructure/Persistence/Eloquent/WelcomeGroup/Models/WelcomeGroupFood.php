<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\Food;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * @property int $id
 * @property int $iiko_menu_item_id
 * @property int $welcome_group_food_category_id
 * @property int $external_id
 * @property int $external_food_category_id
 * @property int $workshop_id
 * @property string $name
 * @property string $description
 * @property int $weight
 * @property int $caloricity
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodCategory $foodCategory
 * @property-read IikoMenuItem $iikoMenuItem
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereCaloricity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereExternalFoodCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereIikoMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereWelcomeGroupFoodCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFood whereWorkshopId($value)
 *
 * @mixin \Eloquent
 */
final class WelcomeGroupFood extends Model
{
    protected $fillable = [
        'iiko_menu_item_id',
        'welcome_group_food_category_id',
        'external_id',
        'external_food_category_id',
        'workshop_id',
        'name',
        'description',
        'weight',
        'caloricity',
        'price',
    ];

    public function iikoMenuItem(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItem::class, 'iiko_menu_item_id', 'id');
    }

    public function foodCategory(): BelongsTo
    {
        return $this->belongsTo(WelcomeGroupFoodCategory::class, 'welcome_group_food_category_id', 'id');
    }

    public function fromDomainEntity(Food $foodCategory): self
    {
        return $this->fill([
            'iiko_menu_item_id' => $foodCategory->iikoItemId->id,
            'welcome_group_food_category_id' => $foodCategory->internalFoodCategoryId->id,
            'external_id' => $foodCategory->externalId->id,
            'external_food_category_id' => $foodCategory->externalFoodCategoryId->id,
            'workshop_id' => $foodCategory->workshopId->id,
            'name' => $foodCategory->name,
            'description' => $foodCategory->description,
            'weight' => $foodCategory->weight,
            'caloricity' => $foodCategory->caloricity,
            'price' => $foodCategory->price,
        ]);
    }

    public static function toDomainEntity(self $iikoMenuItemModifierGroup): Food
    {
        return new Food(
            new IntegerId($iikoMenuItemModifierGroup->id),
            new IntegerId($iikoMenuItemModifierGroup->iiko_menu_item_id),
            new IntegerId($iikoMenuItemModifierGroup->welcome_group_food_category_id),
            new IntegerId($iikoMenuItemModifierGroup->external_id),
            new IntegerId($iikoMenuItemModifierGroup->external_food_category_id),
            new IntegerId($iikoMenuItemModifierGroup->workshop_id),
            $iikoMenuItemModifierGroup->name,
            $iikoMenuItemModifierGroup->description,
            $iikoMenuItemModifierGroup->weight,
            $iikoMenuItemModifierGroup->caloricity,
            $iikoMenuItemModifierGroup->price,
        );
    }
}

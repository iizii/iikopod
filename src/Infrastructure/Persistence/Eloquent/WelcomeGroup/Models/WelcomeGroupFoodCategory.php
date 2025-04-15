<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\FoodCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shared\Domain\ValueObjects\IntegerId;

/**
 *
 *
 * @property int $id
 * @property int $iiko_menu_item_group_id
 * @property int $external_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood> $foods
 * @property-read int|null $foods_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory whereIikoMenuItemGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class WelcomeGroupFoodCategory extends Model
{
    protected $fillable = [
        'iiko_menu_item_group_id',
        'external_id',
        'name',
    ];

    /**
     * @return HasMany<WelcomeGroupFood>
     */
    public function foods(): HasMany
    {
        return $this->hasMany(WelcomeGroupFood::class, 'welcome_group_food_category_id', 'id');
    }

    public function fromDomainEntity(FoodCategory $foodCategory): self
    {
        return $this->fill([
            'iiko_menu_item_group_id' => $foodCategory->iikoItemGroupId->id,
            'external_id' => $foodCategory->externalId->id,
            'name' => $foodCategory->name,
        ]);
    }

    public function toDomainEntity(): FoodCategory
    {
        return new FoodCategory(
            new IntegerId($this->id),
            new IntegerId($this->iiko_menu_item_group_id),
            new IntegerId($this->external_id),
            $this->name,
        );
    }

    public static function toDomainEntityStatic(self $iikoMenuItemModifierGroup): FoodCategory
    {
        return new FoodCategory(
            new IntegerId($iikoMenuItemModifierGroup->id),
            new IntegerId($iikoMenuItemModifierGroup->iiko_menu_item_group_id),
            new IntegerId($iikoMenuItemModifierGroup->external_id),
            $iikoMenuItemModifierGroup->name,
        );
    }
}

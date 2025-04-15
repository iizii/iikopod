<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Entities\FoodModifier;
use Domain\WelcomeGroup\Entities\Modifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Domain\ValueObjects\IntegerId;

/**
 *
 *
 * @property int $id
 * @property int $welcome_group_food_id
 * @property int $welcome_group_modifier_id
 * @property int $external_id
 * @property int $external_food_id
 * @property int $external_modifier_id
 * @property int $weight
 * @property int $caloricity
 * @property int $price
 * @property int $duration
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFood $food
 * @property-read \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier|null $modifier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereCaloricity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereExternalFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereExternalModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereWelcomeGroupFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupFoodModifier whereWelcomeGroupModifierId($value)
 * @mixin \Eloquent
 */
final class WelcomeGroupFoodModifier extends Model
{
    protected $fillable = [
        'welcome_group_food_id',
        'welcome_group_modifier_id',
        'external_id',
        'external_food_id',
        'external_modifier_id',
        'weight',
        'caloricity',
        'price',
        'duration',
    ];

    public function food(): BelongsTo
    {
        return $this->belongsTo(WelcomeGroupFood::class, 'welcome_group_food_id', 'id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(WelcomeGroupModifier::class, 'welcome_group_food_modifier_id', 'id');
    }

    public function casts(): array
    {
        return [
            'weight' => 'integer',
            'caloricity' => 'integer',
            'price' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function fromDomainEntity(FoodModifier $foodModifier): self
    {
        return $this->fill([
            'welcome_group_food_id' => $foodModifier->internalFoodId->id,
            'welcome_group_modifier_id' => $foodModifier->internalModifierId->id,
            'external_id' => $foodModifier->externalId->id,
            'external_food_id' => $foodModifier->externalFoodId->id,
            'external_modifier_id' => $foodModifier->externalModifierId->id,
            'weight' => $foodModifier->weight,
            'caloricity' => $foodModifier->caloricity,
            'price' => $foodModifier->price,
            'duration' => $foodModifier->duration,
        ]);
    }

    public function toDomainEntity(): FoodModifier
    {
        return new FoodModifier(
            new IntegerId($this->id),
            new IntegerId($this->welcome_group_food_id),
            new IntegerId($this->welcome_group_modifier_id),
            new IntegerId($this->external_id),
            new IntegerId($this->external_food_id),
            new IntegerId($this->external_modifier_id),
            $this->weight,
            $this->caloricity,
            $this->price,
            $this->duration,
        );
    }

    public static function toDomainEntityStatic(self $modifier): FoodModifier
    {
        return new FoodModifier(
            new IntegerId($modifier->id),
            new IntegerId($modifier->welcome_group_food_id),
            new IntegerId($modifier->welcome_group_modifier_id),
            new IntegerId($modifier->external_id),
            new IntegerId($modifier->external_food_id),
            new IntegerId($modifier->external_modifier_id),
            $modifier->weight,
            $modifier->caloricity,
            $modifier->price,
            $modifier->duration,
        );
    }
}

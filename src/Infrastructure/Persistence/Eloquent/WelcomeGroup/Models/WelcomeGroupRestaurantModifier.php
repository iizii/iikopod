<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\RestaurantModifier;
use Illuminate\Database\Eloquent\Model;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * 
 *
 * @property int $id
 * @property int $welcome_group_restaurant_id
 * @property int $restaurant_id
 * @property int $welcome_group_modifier_id
 * @property int $modifier_id
 * @property int $external_id
 * @property string $status
 * @property string|null $status_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereStatusComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereWelcomeGroupModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantModifier whereWelcomeGroupRestaurantId($value)
 * @mixin \Eloquent
 */
final class WelcomeGroupRestaurantModifier extends Model
{
    protected $fillable = [
        'id',
        'welcome_group_restaurant_id',
        'restaurant_id',
        'welcome_group_modifier_id',
        'modifier_id',
        'external_id',
        'status',
        'status_comment',
    ];

    public function fromDomainEntity(RestaurantModifier $restaurantModifier): self
    {
        return $this->fill([
            'external_id' => $restaurantModifier->externalId->id,
            'welcome_group_restaurant_id' => $restaurantModifier->welcomeGroupRestaurantId->id,
            'restaurant_id' => $restaurantModifier->restaurantId->id,
            'welcome_group_modifier_id' => $restaurantModifier->modifierId->id,
            'modifier_id' => $restaurantModifier->modifierId->id,
            'status' => $restaurantModifier->status,
            'status_comment' => $restaurantModifier->statusComment,
        ]);
    }

    public static function toDomainEntity(self $restaurantModifier): RestaurantModifier
    {
        return new RestaurantModifier(
            new IntegerId($restaurantModifier->id),
            new IntegerId($restaurantModifier->restaurant_id),
            new IntegerId($restaurantModifier->modifier_id),
            new IntegerId($restaurantModifier->external_id),
            new IntegerId($restaurantModifier->welcome_group_restaurant_id),
            new IntegerId($restaurantModifier->welcome_group_modifier_id),
            $restaurantModifier->status_comment,
            $restaurantModifier->status,
        );
    }
}

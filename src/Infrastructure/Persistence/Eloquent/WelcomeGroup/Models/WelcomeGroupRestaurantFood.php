<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\RestaurantFood;
use Illuminate\Database\Eloquent\Model;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * 
 *
 * @property int $id
 * @property int $welcome_group_restaurant_id
 * @property int $restaurant_id
 * @property int $welcome_group_food_id
 * @property int $food_id
 * @property int $external_id
 * @property string $status
 * @property string|null $status_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereStatusComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereWelcomeGroupFoodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupRestaurantFood whereWelcomeGroupRestaurantId($value)
 * @mixin \Eloquent
 */
final class WelcomeGroupRestaurantFood extends Model
{
    protected $fillable = [
        'id',
        'welcome_group_restaurant_id',
        'restaurant_id',
        'welcome_group_food_id',
        'food_id',
        'external_id',
        'status',
        'status_comment',
    ];

    public function fromDomainEntity(RestaurantFood $restaurantFood): self
    {
        return $this->fill([
            'external_id' => $restaurantFood->externalId->id,
            'welcome_group_restaurant_id' => $restaurantFood->welcomeGroupRestaurantId->id,
            'restaurant_id' => $restaurantFood->restaurantId->id,
            'welcome_group_food_id' => $restaurantFood->welcomeGroupFoodId->id,
            'food_id' => $restaurantFood->foodId->id,
            'status' => $restaurantFood->status,
            'status_comment' => $restaurantFood->statusComment,
        ]);
    }

    public function toDomainEntity(): RestaurantFood
    {
        return new RestaurantFood(
            new IntegerId($this->id),
            new IntegerId($this->restaurant_id),
            new IntegerId($this->food_id),
            new IntegerId($this->external_id),
            new IntegerId($this->welcome_group_restaurant_id),
            new IntegerId($this->welcome_group_food_id),
            $this->status_comment,
            $this->status,
        );
    }

    public static function toDomainEntityStatic(self $restaurantFood): RestaurantFood
    {
        return new RestaurantFood(
            new IntegerId($restaurantFood->id),
            new IntegerId($restaurantFood->restaurant_id),
            new IntegerId($restaurantFood->food_id),
            new IntegerId($restaurantFood->external_id),
            new IntegerId($restaurantFood->welcome_group_restaurant_id),
            new IntegerId($restaurantFood->welcome_group_food_id),
            $restaurantFood->status_comment,
            $restaurantFood->status,
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Illuminate\Database\Eloquent\Model;

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
    //
}

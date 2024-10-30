<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $iiko_api_key
 * @property string $iiko_restaurant_id
 * @property int $welcome_group_restaurant_id
 * @property int $welcome_group_default_workshop_id
 * @property string $order_delivery_type_id
 * @property string $order_pickup_type_id
 * @property array $payment_types
 * @property array $price_categories
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereIikoApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereIikoRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereOrderDeliveryTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereOrderPickupTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting wherePaymentTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting wherePriceCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereWelcomeGroupDefaultWorkshopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrganizationSetting whereWelcomeGroupRestaurantId($value)
 *
 * @mixin \Eloquent
 */
final class OrganizationSetting extends Model
{
    protected $fillable = [
        'iiko_api_key',
        'iiko_restaurant_id',
        'welcome_group_restaurant_id',
        'welcome_group_default_workshop_id',
        'order_delivery_type_id',
        'order_pickup_type_id',
        'payment_types',
        'price_categories',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_types' => 'array',
            'price_categories' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}

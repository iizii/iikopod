<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Settings\Models;

use Domain\Settings\OrganizationSetting as DomainOrganizationSetting;
use Domain\Settings\ValueObjects\PaymentType;
use Domain\Settings\ValueObjects\PaymentTypeCollection;
use Domain\Settings\ValueObjects\PriceCategory;
use Domain\Settings\ValueObjects\PriceCategoryCollection;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property string $iiko_api_key
 * @property string $iiko_restaurant_id
 * @property int $welcome_group_restaurant_id
 * @property int $welcome_group_default_workshop_id
 * @property string $order_delivery_type_id
 * @property string $order_pickup_type_id
 * @property string $external_menu_id
 * @property \Illuminate\Support\Collection $payment_types
 * @property \Illuminate\Support\Collection $price_categories
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereExternalMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereIikoApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereIikoRestaurantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereOrderDeliveryTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereOrderPickupTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting wherePaymentTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting wherePriceCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereWelcomeGroupDefaultWorkshopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationSetting whereWelcomeGroupRestaurantId($value)
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
        'external_menu_id',
        'payment_types',
        'price_categories',
    ];

    public function toDomainEntity(): DomainOrganizationSetting
    {
        return new DomainOrganizationSetting(
            new IntegerId($this->id),
            $this->iiko_api_key,
            new StringId($this->iiko_restaurant_id),
            new StringId($this->external_menu_id),
            new IntegerId($this->welcome_group_restaurant_id),
            new IntegerId($this->welcome_group_default_workshop_id),
            new StringId($this->order_delivery_type_id),
            new StringId($this->order_pickup_type_id),
            new PaymentTypeCollection(
                $this
                    ->payment_types
                    ->map(static fn (array $data): PaymentType => new PaymentType(
                        $data['iiko_payment_code'],
                        $data['welcome_group_payment_code'],
                    )),
            ),
            new PriceCategoryCollection(
                $this
                    ->price_categories
                    ->map(static fn (array $data): PriceCategory => new PriceCategory(
                        new StringId($data['category_id']),
                        $data['prefix'],
                    )),
            ),
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_types' => AsCollection::class,
            'price_categories' => AsCollection::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Iiko\Enums\CustomerType;
use Domain\Orders\Entities\Order as DomainOrder;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\ItemCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Address;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\City;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Coordinates;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\DeliveryPoint;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Region;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Street;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property int $organization_setting_id
 * @property string $source
 * @property string $status
 * @property string|null $iiko_external_id
 * @property int|null $welcome_group_external_id
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $complete_before
 * @property-read OrderCustomer|null $customer
 * @property-read EndpointAddress|null $endpointAddress
 * @property-read Collection<int, OrderItem> $items
 * @property-read int|null $items_count
 * @property-read OrganizationSetting $organizationSetting
 * @property-read Collection<int, OrderPayment> $payments
 * @property-read int|null $payments_count
 *
 * @method static Builder<static>|Order newModelQuery()
 * @method static Builder<static>|Order newQuery()
 * @method static Builder<static>|Order query()
 * @method static Builder<static>|Order whereComment($value)
 * @method static Builder<static>|Order whereCompleteBefore($value)
 * @method static Builder<static>|Order whereCreatedAt($value)
 * @method static Builder<static>|Order whereId($value)
 * @method static Builder<static>|Order whereIikoExternalId($value)
 * @method static Builder<static>|Order whereOrganizationSettingId($value)
 * @method static Builder<static>|Order whereSource($value)
 * @method static Builder<static>|Order whereStatus($value)
 * @method static Builder<static>|Order whereUpdatedAt($value)
 * @method static Builder<static>|Order whereWelcomeGroupExternalId($value)
 *
 * @mixin \Eloquent
 */
final class Order extends Model
{
    protected $fillable = [
        'organization_setting_id',
        'source',
        'status',
        'iiko_external_id',
        'welcome_group_external_id',
        'comment',
        'complete_before',
    ];

    protected $casts = [
        'complete_before' => 'datetime',
    ];

    /**
     * @return HasMany<OrderPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class, 'order_id', 'id');
    }

    /**
     * @return HasOne<OrderCustomer, $this>
     */
    public function customer(): HasOne
    {
        return $this->hasOne(OrderCustomer::class, 'order_id', 'id');
    }

    /**
     * @return HasOne<EndpointAddress, $this>
     */
    public function endpointAddress(): HasOne
    {
        return $this->hasOne(EndpointAddress::class, 'order_id', 'id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /**
     * @return BelongsTo<OrganizationSetting, $this>
     */
    public function organizationSetting(): BelongsTo
    {
        return $this->belongsTo(OrganizationSetting::class);
    }

    public function casts(): array
    {
        return [
            'organization_setting_id' => 'integer',
            'welcome_group_external_id' => 'integer',
        ];
    }

    public function fromDomainEntity(DomainOrder $order): self
    {
        return $this->fill([
            'organization_setting_id' => $order->organizationId->id,
            'source' => $order->source->value,
            'status' => $order->status->value,
            'iiko_external_id' => $order->iikoExternalId->id,
            'welcome_group_external_id' => $order->welcomeGroupExternalId->id,
            'comment' => $order->comment,
            'complete_before' => $order->completeBefore,
        ]);
    }

    public static function toDomainEntity(self $order): DomainOrder
    {
        return new DomainOrder(
            new IntegerId($order->id),
            new IntegerId($order->organization_setting_id),
            OrderSource::from($order->source),
            OrderStatus::from($order->status),
            new StringId($order->iiko_external_id),
            new IntegerId($order->welcome_group_external_id),
            $order->comment,
            $order->payments,
            new Customer(
                $order->customer?->name,
                CustomerType::from($order->customer?->type),
                (string) $order->customer?->phone,
            ),
            new ItemCollection(),
            null,
//            new DeliveryPoint( может это когда-то вернётся, но не сейчас
//                new Coordinates(
//                    (float) $order->endpointAddress?->latitude,
//                    (float) $order->endpointAddress?->longitude
//                ),
//                new Address(
//                    new Street(
//                        null,
//                        null,
//                        $order->endpointAddress?->street,
//                        new City(
//                            null,
//                            $order->endpointAddress?->city,
//                        )
//                    ),
//                    $order->endpointAddress?->index,
//                    $order->endpointAddress?->house,
//                    $order->endpointAddress?->building,
//                    $order->endpointAddress?->flat,
//                    $order->endpointAddress?->entrance,
//                    $order->endpointAddress?->floor,
//                    $order->endpointAddress?->doorphone,
//                    new Region(
//                        null,
//                        $order->endpointAddress?->region,
//                    ),
//                    $order->endpointAddress?->line1,
//                ),
//                null,
//                null
//            ),
            $order->complete_before->toImmutable()
        );
    }
}

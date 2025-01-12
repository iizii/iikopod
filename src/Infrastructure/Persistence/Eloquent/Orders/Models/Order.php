<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Iiko\Enums\CustomerType;
use Domain\Orders\Entities\Order as DomainOrder;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\ItemCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $complete_before
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\OrderCustomer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment|null $payment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCompleteBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIikoExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrganizationSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereWelcomeGroupExternalId($value)
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
     * @return HasOne<OrderPayment>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(OrderPayment::class, 'order_id', 'id');
    }

    /**
     * @return HasOne<OrderCustomer>
     */
    public function customer(): HasOne
    {
        return $this->hasOne(OrderCustomer::class, 'order_id', 'id');
    }

    /**
     * @return HasMany<OrderItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
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
            null,
            new Customer(
                $order->customer->name,
                CustomerType::from($order->customer->type),
                $order->customer->phone,
            ),
            new ItemCollection(),
            $order->complete_before->toImmutable()
        );
    }
}

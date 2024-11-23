<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Orders\Entities\Order as DomainOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $source
 * @property string $status
 * @property string|null $iiko_external_id
 * @property string|null $welcome_group_external_id
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\OrderCustomer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\OrderPayment|null $payment
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIikoExternalId($value)
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
        'source',
        'status',
        'iiko_external_id',
        'welcome_group_external_id',
        'comment',
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

    public function fromDomainEntity(DomainOrder $order): self
    {
        return $this->fill([
            'source' => $order->source,
            'status' => $order->status->value,
            'iiko_external_id' => $order->iikoExternalId->id,
            'welcome_group_external_id' => $order->welcomeGroupExternalId->id,
            'comment' => $order->comment,
        ]);
    }
}

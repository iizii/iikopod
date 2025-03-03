<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Orders\ValueObjects\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property string $type
 * @property string $name
 * @property string $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderCustomer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class OrderCustomer extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'name',
        'phone',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function fromDomainEntity(Customer $customer): self
    {
        $this->newInstance();

        return $this->fill([
            'type' => $customer->type->value,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Orders\ValueObjects\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property string $type
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\Order $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPayment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'amount',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function fromDomainEntity(Payment $payment): self
    {
        $this->newInstance();

        return $this->fill([
            'type' => $payment->type,
            'amount' => $payment->amount,
        ]);
    }
}

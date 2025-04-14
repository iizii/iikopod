<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Orders\ValueObjects\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem;

/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property int $iiko_menu_item_id
 * @property int $price
 * @property int $discount
 * @property int $amount
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read IikoMenuItem $iikoMenuItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier> $modifiers
 * @property-read int|null $modifiers_count
 * @property-read \Infrastructure\Persistence\Eloquent\Orders\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereIikoMenuItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'iiko_menu_item_id',
        'price',
        'discount',
        'amount',
        'comment',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function iikoMenuItem(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItem::class, 'iiko_menu_item_id', 'id');
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(OrderItemModifier::class, 'order_item_id', 'id');
    }

    public function fromDomainEntity(Item $item): self
    {
        $this->newInstance();

        return $this->fill([
            'iiko_menu_item_id' => $item->itemId->id,
            'price' => $item->price,
            'discount' => $item->discount,
            'amount' => $item->amount,
            'comment' => $item->comment,
        ]);
    }
}

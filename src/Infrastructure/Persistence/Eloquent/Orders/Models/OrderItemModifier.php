<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\Orders\Models;

use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\Modifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;

/**
 * @property int $id
 * @property int $order_item_id
 * @property int $iiko_menu_item_modifier_item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static >|OrderItemModifier
 *     whereIikoMenuItemModifierItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemModifier whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class OrderItemModifier extends Model
{
    protected $fillable = [
        'order_item_id',
        'iiko_menu_item_modifier_item_id',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'id');
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemModifierItem::class, 'iiko_menu_item_modifier_item_id', 'id');
    }

    public function fromDomainEntity(Modifier $modifier): self
    {
        $this->newInstance();

        return $this->fill([
            'iiko_menu_item_modifier_item_id' => $modifier->modifierId->id,
        ]);
    }
}

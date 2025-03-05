<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Price;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * @property int $id
 * @property int $iiko_menu_item_size_id
 * @property int|null $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize $itemSize
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice whereIikoMenuItemSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemPrice whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemPrice extends Model
{
    protected $fillable = [
        'iiko_menu_item_size_id',
        'price',
    ];

    public function itemSize(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemSize::class, 'iiko_menu_item_size_id', 'id');
    }

    public function fromDomainEntity(Price $price): self
    {
        return $this->fill([
            'iiko_menu_item_size_id' => $price->itemId->id,
            'price' => $price->price,
        ]);
    }

    public static function toDomainEntity(self $iikoMenuItemPrice): Price
    {
        return new Price(
            new IntegerId($iikoMenuItemPrice->id),
            new IntegerId($iikoMenuItemPrice->iiko_menu_item_size_id),
            $iikoMenuItemPrice->price,
        );
    }

    public function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }
}

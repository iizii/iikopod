<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property int $iiko_menu_item_modifier_group_id
 * @property string $external_id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property string|null $type
 * @property string|null $measure_unit
 * @property string|null $payment_subject
 * @property bool $is_hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierGroup $modifierGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItemPrice> $prices
 * @property-read int|null $prices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereIikoMenuItemModifierGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereMeasureUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem wherePaymentSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemModifierItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenuItemModifierItem extends Model
{
    protected $fillable = [
        'iiko_menu_item_modifier_group_id',
        'external_id',
        'sku',
        'name',
        'description',
        'type',
        'measure_unit',
        'payment_subject',
        'is_hidden',
    ];

    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemModifierGroup::class, 'iiko_menu_item_modifier_group_id', 'id');
    }

    /**
     * @return HasMany<IikoMenuItemModifierItemPrice>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(IikoMenuItemModifierItemPrice::class, 'iiko_menu_item_modifier_item_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    public function fromDomainEntity(Item $item): self
    {
        return $this->fill([
            'iiko_menu_item_modifier_group_id' => $item->itemGroupId->id,
            'external_id' => $item->externalId->id,
            'sku' => $item->sku,
            'name' => $item->name,
            'description' => $item->description,
            'type' => $item->type,
            'measure_unit' => $item->measureUnit,
            'payment_subject' => $item->paymentSubject,
            'is_hidden' => $item->isHidden,
        ]);
    }

    public static function toDomainEntity(self $iikoMenuItemModifierItem): Item
    {
        return new Item(
            new IntegerId($iikoMenuItemModifierItem->id),
            new IntegerId($iikoMenuItemModifierItem->iiko_menu_item_modifier_group_id),
            new StringId($iikoMenuItemModifierItem->external_id),
            $iikoMenuItemModifierItem->sku,
            $iikoMenuItemModifierItem->name,
            $iikoMenuItemModifierItem->description,
            $iikoMenuItemModifierItem->type,
            $iikoMenuItemModifierItem->measure_unit,
            $iikoMenuItemModifierItem->payment_subject,
            $iikoMenuItemModifierItem->is_hidden,
            new PriceCollection(),
            new ItemSizeCollection(),
        );
    }
}

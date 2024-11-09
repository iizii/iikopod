<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\ValueObjects\Menu\ItemSizeCollection;
use Domain\Iiko\ValueObjects\Menu\PriceCollection;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Observers\Iiko\ItemObserver;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property int $iiko_menu_item_group_id
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
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup $itemGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemSize> $itemSizes
 * @property-read int|null $item_sizes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereIikoMenuItemGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereMeasureUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem wherePaymentSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ItemObserver::class])]
final class IikoMenuItem extends Model
{
    protected $fillable = [
        'iiko_menu_item_group_id',
        'external_id',
        'sku',
        'name',
        'description',
        'type',
        'measure_unit',
        'payment_subject',
        'is_hidden',
    ];

    public function itemGroup(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemGroup::class, 'iiko_menu_item_group_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItemSize>
     */
    public function itemSizes(): HasMany
    {
        return $this->hasMany(IikoMenuItemSize::class, 'iiko_menu_item_id', 'id');
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
            'iiko_menu_item_group_id' => $item->itemGroupId->id,
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

    public static function toDomainEntity(self $iikoMenuItem): Item
    {
        return new Item(
            new IntegerId($iikoMenuItem->id),
            new IntegerId($iikoMenuItem->iiko_menu_item_group_id),
            new StringId($iikoMenuItem->external_id),
            $iikoMenuItem->sku,
            $iikoMenuItem->name,
            $iikoMenuItem->description,
            $iikoMenuItem->type,
            $iikoMenuItem->measure_unit,
            $iikoMenuItem->payment_subject,
            $iikoMenuItem->is_hidden,
            new PriceCollection(),
            new ItemSizeCollection(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\ItemGroup;
use Domain\Iiko\ValueObjects\Menu\ItemCollection;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Observers\Iiko\ItemGroupObserver;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property int $iiko_menu_id
 * @property string $external_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenu $iikoMenu
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItem> $items
 * @property-read int|null $items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereIikoMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenuItemGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[ObservedBy([ItemGroupObserver::class])]
final class IikoMenuItemGroup extends Model
{
    protected $fillable = [
        'iiko_menu_id',
        'external_id',
        'name',
        'description',
        'is_hidden',
    ];

    public function iikoMenu(): BelongsTo
    {
        return $this->belongsTo(IikoMenu::class, 'iiko_menu_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(IikoMenuItem::class, 'iiko_menu_item_group_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    public function fromDomainEntity(ItemGroup $itemGroup): self
    {
        return $this->fill([
            'iiko_menu_id' => $itemGroup->iikoMenuId->id,
            'external_id' => $itemGroup->externalId->id,
            'name' => $itemGroup->name,
            'description' => $itemGroup->description,
            'is_hidden' => $itemGroup->isHidden,
        ]);
    }

    public static function toDomainEntity(self $iikoMenuItemGroup): ItemGroup
    {
        return new ItemGroup(
            new IntegerId($iikoMenuItemGroup->id),
            new IntegerId($iikoMenuItemGroup->iiko_menu_id),
            new StringId($iikoMenuItemGroup->external_id),
            $iikoMenuItemGroup->name,
            $iikoMenuItemGroup->description,
            $iikoMenuItemGroup->is_hidden,
            new ItemCollection(),
        );
    }
}

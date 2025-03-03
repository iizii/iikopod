<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\ValueObjects\Menu\ItemGroupCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Persistence\Eloquent\Settings\Models\OrganizationSetting;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * 
 *
 * @property int $id
 * @property int $organization_setting_id
 * @property string $external_id
 * @property int $revision
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup> $itemGroups
 * @property-read int|null $item_groups_count
 * @property-read OrganizationSetting $organizationSetting
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereOrganizationSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereRevision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IikoMenu whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class IikoMenu extends Model
{
    protected $fillable = [
        'organization_setting_id',
        'external_id',
        'revision',
        'name',
        'description',
    ];

    /**
     * @return HasMany<array-key, IikoMenuItemGroup>
     */
    public function itemGroups(): HasMany
    {
        return $this->hasMany(IikoMenuItemGroup::class, 'iiko_menu_id', 'id');
    }

    public function organizationSetting(): BelongsTo
    {
        return $this->belongsTo(OrganizationSetting::class);
    }

    public function fromDomainEntity(Menu $menu): self
    {
        return $this->fill([
            'organization_setting_id' => $menu->organizationSettingId->id,
            'external_id' => $menu->externalId->id,
            'revision' => $menu->revision,
            'name' => $menu->name,
            'description' => $menu->description,
        ]);
    }

    public static function toDomainEntity(self $iikoMenu): Menu
    {
        return new Menu(
            new IntegerId($iikoMenu->id),
            new IntegerId($iikoMenu->organization_setting_id),
            new StringId($iikoMenu->external_id),
            $iikoMenu->revision,
            $iikoMenu->name,
            $iikoMenu->description,
            new ItemGroupCollection(),
        );
    }
}

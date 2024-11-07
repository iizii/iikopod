<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\IIko\Models\Menu;

use Domain\Iiko\Entities\Menu\Menu;
use Domain\Iiko\ValueObjects\Menu\ItemGroupCollection;
use Domain\Iiko\ValueObjects\Menu\ProductCategoryCollection;
use Domain\Iiko\ValueObjects\Menu\TaxCategoryCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property string $external_id
 * @property int $revision
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemGroup> $itemGroups
 * @property-read int|null $item_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuProductCategory> $productCategories
 * @property-read int|null $product_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuTaxCategory> $taxCategories
 * @property-read int|null $tax_categories_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereRevision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IikoMenu whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class IikoMenu extends Model
{
    protected $fillable = [
        'external_id',
        'revision',
        'name',
        'description',
    ];

    /**
     * @return HasMany<array-key, IikoMenuTaxCategory>
     */
    public function taxCategories(): HasMany
    {
        return $this->hasMany(IikoMenuTaxCategory::class, 'iiko_menu_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuProductCategory>
     */
    public function productCategories(): HasMany
    {
        return $this->hasMany(IikoMenuProductCategory::class, 'iiko_menu_id', 'id');
    }

    /**
     * @return HasMany<array-key, IikoMenuItemGroup>
     */
    public function itemGroups(): HasMany
    {
        return $this->hasMany(IikoMenuItemGroup::class, 'iiko_menu_id', 'id');
    }

    public function fromDomainEntity(Menu $menu): self
    {
        return $this->fill([
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
            new StringId($iikoMenu->external_id),
            $iikoMenu->revision,
            $iikoMenu->name,
            $iikoMenu->description,
            new TaxCategoryCollection(),
            new ProductCategoryCollection(),
            new ItemGroupCollection(),
        );
    }
}

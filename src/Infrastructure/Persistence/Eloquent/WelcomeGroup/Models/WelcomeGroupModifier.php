<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\Modifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infrastructure\Persistence\Eloquent\IIko\Models\Menu\IikoMenuItemModifierItem;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

/**
 * @property int $id
 * @property int $welcome_group_modifier_type_id
 * @property int $iiko_menu_item_modifier_item_id
 * @property int $external_id
 * @property int $external_modifier_type_id
 * @property string $name
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $iiko_external_modifier_id
 * @property-read IikoMenuItemModifierItem $iikoModifier
 * @property-read \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType $modifierType
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereExternalModifierTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereIikoExternalModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereIikoMenuItemModifierItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WelcomeGroupModifier whereWelcomeGroupModifierTypeId($value)
 *
 * @mixin \Eloquent
 */
final class WelcomeGroupModifier extends Model
{
    protected $fillable = [
        'welcome_group_modifier_type_id',
        'external_id',
        'external_modifier_type_id',
        'name',
        'is_default',
        'iiko_menu_item_modifier_item_id',
        'iiko_external_modifier_id',
    ];

    /**
     * @return BelongsTo<WelcomeGroupModifierType>
     */
    public function modifierType(): BelongsTo
    {
        return $this->belongsTo(WelcomeGroupModifierType::class, 'welcome_group_modifier_type_id', 'id');
    }

    /**
     * @return BelongsTo<WelcomeGroupModifierType>
     */
    public function iikoModifier(): BelongsTo
    {
        return $this->belongsTo(IikoMenuItemModifierItem::class, 'iiko_menu_item_modifier_item_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'welcome_group_modifier_type_id' => 'integer',
        ];
    }

    public function fromDomainEntity(Modifier $modifier): self
    {
        return $this->fill([
            'welcome_group_modifier_type_id' => $modifier->internalModifierTypeId->id,
            'iiko_menu_item_modifier_item_id' => $modifier->internalIikoItemId->id,
            'external_id' => $modifier->externalId->id,
            'external_modifier_type_id' => $modifier->externalModifierTypeId->id,
            'iiko_external_modifier_id' => $modifier->iikoExternalModifierId->id,
            'name' => $modifier->name,
            'is_default' => $modifier->isDefault,
        ]);
    }

    public static function toDomainEntity(self $modifier): Modifier
    {
        return new Modifier(
            new IntegerId($modifier->id),
            new IntegerId($modifier->welcome_group_modifier_type_id),
            new IntegerId($modifier->iiko_menu_item_modifier_item_id),
            new IntegerId($modifier->external_id),
            new IntegerId($modifier->external_modifier_type_id),
            new StringId($modifier->iiko_external_modifier_id),
            $modifier->name,
            $modifier->is_default,
        );
    }
}

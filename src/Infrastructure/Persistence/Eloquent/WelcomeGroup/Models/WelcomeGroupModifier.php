<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\Modifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * @property int $id
 * @property int $welcome_group_modifier_type_id
 * @property int $external_id
 * @property int $external_modifier_type_id
 * @property string $name
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType $modifierType
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier query()
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereExternalModifierTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifier whereWelcomeGroupModifierTypeId($value)
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
    ];

    public function modifierType(): BelongsTo
    {
        return $this->belongsTo(WelcomeGroupModifierType::class, 'welcome_group_modifier_type_id', 'id');
    }

    public function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function fromDomainEntity(Modifier $modifier): self
    {
        return $this->fill([
            'welcome_group_modifier_type_id' => $modifier->internalModifierTypeId->id,
            'external_id' => $modifier->externalId->id,
            'external_modifier_type_id' => $modifier->externalModifierTypeId->id,
            'name' => $modifier->name,
            'is_default' => $modifier->isDefault,
        ]);
    }

    public static function toDomainEntity(self $modifier): Modifier
    {
        return new Modifier(
            new IntegerId($modifier->id),
            new IntegerId($modifier->welcome_group_modifier_type_id),
            new IntegerId($modifier->external_id),
            new IntegerId($modifier->external_modifier_type_id),
            $modifier->name,
            $modifier->is_default,
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent\WelcomeGroup\Models;

use Domain\WelcomeGroup\Entities\ModifierType;
use Domain\WelcomeGroup\Enums\ModifierTypeBehaviour;
use Illuminate\Database\Eloquent\Model;
use Shared\Domain\ValueObjects\IntegerId;

/**
 * @property int $id
 * @property int $external_id
 * @property string $name
 * @property string $behaviour
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType query()
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType whereBehaviour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WelcomeGroupModifierType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
final class WelcomeGroupModifierType extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'behaviour',
    ];

    public function fromDomainEntity(ModifierType $modifierType): self
    {
        return $this->fill([
            'external_id' => $modifierType->externalId->id,
            'name' => $modifierType->name,
            'behaviour' => $modifierType->behaviour->value,
        ]);
    }

    public static function toDomainEntity(self $groupModifierType): ModifierType
    {
        return new ModifierType(
            new IntegerId($groupModifierType->id),
            new IntegerId($groupModifierType->external_id),
            $groupModifierType->name,
            ModifierTypeBehaviour::from($groupModifierType->behaviour),
        );
    }
}

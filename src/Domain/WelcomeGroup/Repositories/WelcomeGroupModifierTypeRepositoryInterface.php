<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\ModifierType;
use Illuminate\Database\Eloquent\Collection;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupModifierTypeRepositoryInterface
{
    public function save(ModifierType $modifierType): ModifierType;

    public function update(ModifierType $modifierType): ModifierType;

    public function getByIikoModifierGroupIdAndName(IntegerId $iikoModifierGroupId, string $groupName): Collection;
}

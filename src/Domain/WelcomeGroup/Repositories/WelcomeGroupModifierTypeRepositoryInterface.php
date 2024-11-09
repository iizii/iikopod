<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\ModifierType;

interface WelcomeGroupModifierTypeRepositoryInterface
{
    public function save(ModifierType $modifierType): ModifierType;
}

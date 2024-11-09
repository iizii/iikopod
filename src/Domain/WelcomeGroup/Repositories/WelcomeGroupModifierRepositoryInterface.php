<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Modifier;

interface WelcomeGroupModifierRepositoryInterface
{
    public function save(Modifier $modifierType): Modifier;
}

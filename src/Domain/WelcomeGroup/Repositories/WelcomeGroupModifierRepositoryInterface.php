<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Modifier;
use Shared\Domain\ValueObjects\IntegerId;

interface WelcomeGroupModifierRepositoryInterface
{
    public function save(Modifier $modifierType): Modifier;

    public function findByIikoId(IntegerId $id): ?Modifier;
}

<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Repositories;

use Domain\WelcomeGroup\Entities\Modifier;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

interface WelcomeGroupModifierRepositoryInterface
{
    public function save(Modifier $modifier): Modifier;

    public function update(Modifier $modifier): Modifier;

    public function findById(IntegerId $id): ?Modifier;

    public function findByInternalModifierTypeIdAndIikoExternalId(IntegerId $internalModifierTypeId, StringId $externalIikoModifierId): ?Modifier;
    public function findByIikoId(IntegerId $id): ?Modifier;
}

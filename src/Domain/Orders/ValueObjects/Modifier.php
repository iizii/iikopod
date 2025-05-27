<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Modifier extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $itemId, // (id во внутренних системах)
        public readonly IntegerId $modifierId, // (id во внутренних системах)
        public readonly ?StringId $positionId = null, // id позиции во внешней системе iiko
        public readonly ?IntegerId $welcomeGroupExternalId = null, // id позиции во внешней системе wg (ПОД)
    ) {}
}

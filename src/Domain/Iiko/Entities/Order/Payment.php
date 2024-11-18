<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Order;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Payment extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly string $kind,
        public readonly int $sum,
        public readonly bool $isPreliminary,
        public readonly bool $isExternal,
        public readonly bool $isProcessedExternally,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay,
    ) {}
}

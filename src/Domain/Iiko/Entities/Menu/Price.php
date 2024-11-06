<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Price extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $itemId,
        public readonly ?int $price,
    ) {}

    public static function withItemId(self $price, IntegerId $itemId): self
    {
        return new self(
            $price->id,
            $itemId,
            $price->price,
        );
    }
}

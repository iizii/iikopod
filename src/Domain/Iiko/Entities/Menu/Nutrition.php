<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Nutrition extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $itemSizeId,
        public readonly float $fats,
        public readonly float $proteins,
        public readonly float $carbs,
        public readonly float $energy,
        public readonly ?float $saturatedFattyAcid,
        public readonly ?float $salt,
        public readonly ?float $sugar,
    ) {}
}

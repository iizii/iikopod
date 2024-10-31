<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Shared\Domain\DomainEntity;

final class Nutrition extends DomainEntity
{
    /**
     * @param  array<array-key, string>  $organizations
     */
    public function __construct(
        public readonly float $fats,
        public readonly float $proteins,
        public readonly float $carbs,
        public readonly float $energy,
        public readonly array $organizations,
        public readonly ?float $saturatedFattyAcid,
        public readonly ?float $salt,
        public readonly ?float $sugar
    ) {}
}

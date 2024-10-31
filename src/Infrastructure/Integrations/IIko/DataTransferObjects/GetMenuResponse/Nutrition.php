<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\ValueObjects\Menu\Nutrition as DomainNutrition;
use Shared\Infrastructure\Integrations\ResponseData;

final class Nutrition extends ResponseData
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
        public readonly ?float $sugar,
    ) {}

    public function toDomainEntity(): DomainNutrition
    {
        return new DomainNutrition(
            $this->fats,
            $this->proteins,
            $this->carbs,
            $this->energy,
            $this->organizations,
            $this->saturatedFattyAcid,
            $this->salt,
            $this->sugar,
        );
    }
}

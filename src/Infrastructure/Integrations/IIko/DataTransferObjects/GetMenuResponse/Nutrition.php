<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Nutrition as DomainNutrition;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class Nutrition extends ResponseData
{
    public function __construct(
        public readonly float $fats,
        public readonly float $proteins,
        public readonly float $carbs,
        public readonly float $energy,
        public readonly ?float $saturatedFattyAcid,
        public readonly ?float $salt,
        public readonly ?float $sugar,
    ) {}

    public function toDomainEntity(): DomainNutrition
    {
        return new DomainNutrition(
            new IntegerId(),
            new IntegerId(),
            new StringId(),
            $this->fats,
            $this->proteins,
            $this->carbs,
            $this->energy,
            $this->saturatedFattyAcid,
            $this->salt,
            $this->sugar,
        );
    }
}

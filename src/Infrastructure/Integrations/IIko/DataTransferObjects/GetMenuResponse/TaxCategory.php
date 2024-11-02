<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\TaxCategory as DomainTaxCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class TaxCategory extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?int $percentage,
    ) {}

    public function toDomainEntity(): DomainTaxCategory
    {
        return new DomainTaxCategory(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->name,
            $this->percentage,
        );
    }
}

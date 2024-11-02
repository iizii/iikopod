<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\ProductCategory as DomainProductCategory;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class ProductCategory extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $isDeleted,
        public readonly ?int $percentage,
    ) {}

    public function toDomainEntity(): DomainProductCategory
    {
        return new DomainProductCategory(
            new IntegerId(),
            new IntegerId(),
            new StringId($this->id),
            $this->name,
            $this->isDeleted,
            $this->percentage,
        );
    }
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Menu;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class ProductCategory extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $iikoMenuId,
        public readonly StringId $externalId,
        public readonly string $name,
        public readonly bool $isDeleted,
        public readonly ?int $percentage,
    ) {}

    public static function withMenuIdAndPrefix(self $productCategory, IntegerId $menuId, string $prefix): self
    {
        return new self(
            $productCategory->id,
            $menuId,
            $productCategory->externalId,
            sprintf('%s %s', $prefix, $productCategory->name),
            $productCategory->isDeleted,
            $productCategory->percentage,
        );
    }
}

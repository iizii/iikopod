<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\AllergenGroup as DomainAllergenGroup;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class AllergenGroup extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
    ) {}

    public function toDomainEntity(): DomainAllergenGroup
    {
        return new DomainAllergenGroup(
            new StringId($this->id),
            $this->code,
            $this->name,
        );
    }
}

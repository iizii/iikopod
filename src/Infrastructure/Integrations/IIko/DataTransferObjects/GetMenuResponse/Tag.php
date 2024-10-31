<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Tag as DomainTag;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class Tag extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}

    public function toDomainEntity(): DomainTag
    {
        return new DomainTag(
            new StringId($this->id),
            $this->name,
        );
    }
}

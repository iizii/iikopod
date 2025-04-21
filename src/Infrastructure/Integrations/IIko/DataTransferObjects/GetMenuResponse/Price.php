<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\Entities\Menu\Price as DomainPrice;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class Price extends ResponseData
{
    /**
     * @param  array<array-key, string>  $organizations
     */
    public function __construct(
        public readonly array $organizations,
        public readonly ?int $price,
    ) {}

    public function toDomainEntity(): DomainPrice
    {
        return new DomainPrice(
            new IntegerId(),
            new IntegerId(),
            new StringId(),
            $this->price,
        );
    }
}

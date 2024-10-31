<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse;

use Domain\Iiko\ValueObjects\Menu\OrganizationIdCollection;
use Domain\Iiko\ValueObjects\Menu\Price as DomainPrice;
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
        $organizationIds = new OrganizationIdCollection();

        foreach ($this->organizations as $organizationId) {
            $organizationIds->add(new StringId($organizationId));
        }

        return new DomainPrice(
            $organizationIds,
            $this->price,
        );
    }
}

<?php

declare(strict_types=1);

namespace Domain\Orders\Entities;

use Carbon\CarbonImmutable;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\Enums\OrderStatus;
use Domain\Orders\ValueObjects\Customer;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\ItemCollection;
use Domain\Orders\ValueObjects\Payment;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\DeliveryPoint;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Order extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $organizationId,
        public readonly OrderSource $source,
        public readonly OrderStatus $status,
        public readonly StringId $iikoExternalId,
        public readonly IntegerId $welcomeGroupExternalId,
        public readonly ?string $comment,
        public readonly ?Payment $payment,
        public readonly Customer $customer,
        public readonly ItemCollection $items,
        public readonly ?DeliveryPoint $deliveryPoint,
        public readonly ?CarbonImmutable $completeBefore,
    ) {}

    public function addItem(Item $item): self
    {
        if (! $this->items->contains($item)) {
            $this->items->add($item);
        }

        return $this;
    }
}

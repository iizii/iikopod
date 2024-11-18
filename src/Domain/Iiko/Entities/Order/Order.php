<?php

declare(strict_types=1);

namespace Domain\Iiko\Entities\Order;

use Domain\Iiko\Enums\OrderCreationStatus;
use Domain\Iiko\Enums\OrderStatus;
use Domain\Iiko\ValueObjects\Order\ItemCollection;
use Domain\Iiko\ValueObjects\Order\PaymentCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Order extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly StringId $externalId,
        public readonly StringId $parentDeliveryId,
        public readonly StringId $organizationId,
        public readonly OrderCreationStatus $creationStatus,
        public readonly string $phone,
        public readonly Customer $customer,
        public readonly OrderStatus $status,
        public readonly ItemCollection $items,
        public readonly PaymentCollection $payments,
    ) {}
}

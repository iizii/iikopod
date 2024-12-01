<?php

declare(strict_types=1);

namespace Domain\Orders\Repositories;

use Domain\Orders\Entities\Order;
use Domain\Orders\Entities\Order as DomainOrder;
use Shared\Domain\ValueObjects\StringId;

interface OrderRepositoryInterface
{
    public function store(Order $order): Order;

    public function update(DomainOrder $order): ?DomainOrder;

    public function findByIikoId(StringId $id): ?DomainOrder;
}

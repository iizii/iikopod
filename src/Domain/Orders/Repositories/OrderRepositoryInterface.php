<?php

declare(strict_types=1);

namespace Domain\Orders\Repositories;

use Domain\Orders\Entities\Order;

interface OrderRepositoryInterface
{
    public function store(Order $order): Order;
}

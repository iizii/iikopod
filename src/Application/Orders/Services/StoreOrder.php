<?php

declare(strict_types=1);

namespace Application\Orders\Services;

use Domain\Orders\Entities\Order;
use Domain\Orders\Events\OrderCreatedEvent;
use Domain\Orders\Exceptions\OrderStoreException;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;

final readonly class StoreOrder
{
    public function __construct(
        private OrderRepositoryInterface $repository,
        private DatabaseManager $databaseManager,
        private Dispatcher $dispatcher,
    ) {}

    /**
     * @throws \Throwable
     */
    public function store(Order $order): Order
    {
        $this->databaseManager->beginTransaction();

        try {
            $order = $this->repository->store($order);

            $this->dispatcher->dispatch(new OrderCreatedEvent($order));

            $this->databaseManager->commit();

        } catch (\Throwable $exception) {
            $this->databaseManager->rollBack();

            throw new OrderStoreException($exception->getMessage());
        }

        return $order;
    }
}

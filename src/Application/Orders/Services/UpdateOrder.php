<?php

declare(strict_types=1);

namespace Application\Orders\Services;

use Domain\Orders\Entities\Order;
use Domain\Orders\Events\OrderUpdatedEvent;
use Domain\Orders\Exceptions\OrderStoreException;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;

final readonly class UpdateOrder
{
    public function __construct(
        private OrderRepositoryInterface $repository,
        private DatabaseManager $databaseManager,
        private Dispatcher $dispatcher,
    ) {}

    /**
     * @throws \Throwable
     */
    public function update(Order $order): Order
    {
        $this->databaseManager->beginTransaction();

        try {
            $this->repository->update($order);

            $this->dispatcher->dispatch(new OrderUpdatedEvent($order));

            $this->databaseManager->commit();

        } catch (\Throwable $exception) {
            $this->databaseManager->rollBack();

            throw new OrderStoreException($exception->getMessage(), 500, $exception);
        }

        return $order;
    }
}

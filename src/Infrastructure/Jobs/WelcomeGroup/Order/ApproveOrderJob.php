<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup\Order;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Settings\Exceptions\OrganizationNotFoundException;
use Domain\Settings\Interfaces\OrganizationSettingRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class ApproveOrderJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Order $order)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     *
     * @throws OrganizationNotFoundException
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        OrganizationSettingRepositoryInterface $organizationSettingRepository,
    ): void {
        $order = $this->order;

        $organizationSettings = $organizationSettingRepository->findById($order->organizationId);

        if (! $organizationSettings) {
            throw new OrganizationNotFoundException(
                sprintf(
                    'Не найден ресторан %s при создании заказа %s в Welcome Group',
                    $order->organizationId,
                    $order->id,
                ),
            );
        }

        $order = \Infrastructure\Persistence\Eloquent\Orders\Models\Order::query()
            ->find($order->id->id);

        try {
            $response = $welcomeGroupConnector->approveOrder(new IntegerId($order->welcome_group_external_id));
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При апруве заказа %s в Welcome Group произошла ошибка, ошибка: %s',
                    $order->id->id,
                    $e->getMessage(),
                ),
            );
        }
    }

    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): int
    {
        return 60;
    }
}

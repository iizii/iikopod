<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup\Order;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Exceptions\OrderNotFoundException;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\Orders\ValueObjects\Item;
use Domain\Orders\ValueObjects\Modifier;
use Domain\WelcomeGroup\Exceptions\FoodModifierNotFoundException;
use Domain\WelcomeGroup\Exceptions\FoodNotFoundException;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Queue\Queue;

final class CreateOrderItemJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Order $order, public readonly Item $item)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * @throws ConnectionException
     * @throws RequestException|OrderNotFoundException
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodRepositoryInterface $welcomeGroupFoodRepository,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        OrderRepositoryInterface $orderRepository,
    ): void {
        $item = $this->item;

        $order = $orderRepository->findByIikoId($this->order->iikoExternalId);

        if (! $order) {
            throw new OrderNotFoundException();
        }

        $food = $welcomeGroupFoodRepository->findByIikoId($item->itemId);

        if (! $food) {
            throw new FoodNotFoundException();
        }

        $modifierIds = new Collection();

        $item->modifiers->each(
            static function (Modifier $modifier) use ($modifierIds, $welcomeGroupModifierRepository) {
                $foundModifier = $welcomeGroupModifierRepository->findByIikoId($modifier->modifierId);

                if (! $foundModifier) {
                    throw new FoodModifierNotFoundException();
                }

                $modifierIds->add($foundModifier->externalId->id);
            },
        );

        $welcomeGroupConnector->createOrderItem(
            new CreateOrderItemRequestData(
                $order->welcomeGroupExternalId->id,
                $food->externalId->id,
                $modifierIds->toArray(),
            ),
        );
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

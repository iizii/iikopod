<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup\Order;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Orders\Entities\Order;
use Domain\Orders\Exceptions\OrderNotFoundException;
use Domain\Orders\Repositories\OrderRepositoryInterface;
use Domain\WelcomeGroup\Exceptions\FoodModifierNotFoundException;
use Domain\WelcomeGroup\Exceptions\FoodNotFoundException;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItem;
use Infrastructure\Persistence\Eloquent\Orders\Models\OrderItemModifier;
use Infrastructure\Queue\Queue;
use Shared\Domain\ValueObjects\IntegerId;

final class CreateOrderItemJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Order $order, public readonly OrderItem $item, public readonly string $sourceKey)
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
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
        OrderRepositoryInterface $orderRepository,
    ): void {
        $item = $this->item;

        $order = $orderRepository->findByIikoId($this->order->iikoExternalId);

        if (! $order) {
            throw new OrderNotFoundException();
        }

        $food = $welcomeGroupFoodRepository->findByIikoId(new IntegerId($item->iiko_menu_item_id));

        if (! $food) {
            throw new FoodNotFoundException();
        }

        $modifierIds = new Collection();
        $item->load('modifiers');
        $item->modifiers->each(
            static function (OrderItemModifier $modifier) use ($modifierIds, $welcomeGroupModifierRepository, $welcomeGroupFoodModifierRepository, $food) {
                $foundModifier = $welcomeGroupModifierRepository->findByIikoId(new IntegerId($modifier->iiko_menu_item_modifier_item_id));

                if (! $foundModifier) {
                    throw new FoodModifierNotFoundException();
                }

                $foundFoodModifier = $welcomeGroupFoodModifierRepository->findByInternalFoodAndModifierIds($food->id, $foundModifier->id);

                if (! $foundFoodModifier) {
                    throw new FoodModifierNotFoundException();
                }

                $modifierIds->add($foundFoodModifier->externalId->id);
            },
        );

        try {
            $orderItem = $welcomeGroupConnector->createOrderItem(
                new CreateOrderItemRequestData(
                    (int) $order->welcomeGroupExternalId->id,
                    (int) $food->externalId->id,
                    $modifierIds->toArray(),
                ),
            );

            $item->update([
                'welcome_group_external_id' => $orderItem->id,
                'welcome_group_external_food_id' => $orderItem->food,
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При создании блюда %s для заказа %s произошла ошибка: %s',
                    $food->name,
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

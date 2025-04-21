<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\WelcomeGroup\Builders\FoodModifierBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Exceptions\PriceNotLoadedException;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Entities\Modifier;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierRequestData;
use Infrastructure\Queue\Queue;

final class CreateFoodModifierJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public $delay = 150;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Food $food,
        public readonly Item $item,
        public readonly Modifier $modifier,
    ) {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodModifierRepositoryInterface $welcomeGroupFoodModifierRepository,
    ): void {
        $itemPrice = $this->item->prices->first();

        if (! $itemPrice) {
            throw new PriceNotLoadedException(sprintf('Price not loaded for item %s', $this->item->id->id));
        }

        $createFoodModifierResponse = $welcomeGroupConnector->createFoodModifier(
            new CreateFoodModifierRequestData(
                $this->food->externalId->id,
                $this->modifier->externalId->id,
                $this->item->weight,
                $itemPrice->price ?? 0,
            ),
        );

        $foodModifierBuilder = FoodModifierBuilder::fromExisted(
            $createFoodModifierResponse->toDomainEntity(),
        );
        $foodModifierBuilder = $foodModifierBuilder
            ->setInternalModifierId($this->modifier->id)
            ->setInternalFoodId($this->food->id);

        $welcomeGroupFoodModifierRepository->save($foodModifierBuilder->build());
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

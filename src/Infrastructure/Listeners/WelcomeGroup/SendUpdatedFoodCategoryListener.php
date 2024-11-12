<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Application\WelcomeGroup\Builders\FoodCategoryBuilder;
use Domain\Iiko\Events\ItemGroupUpdatedEvent;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\UpdateFoodCategoryJob;

final readonly class SendUpdatedFoodCategoryListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private Dispatcher $dispatcher,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ItemGroupUpdatedEvent $event): void
    {
        $iikoItemGroup = $event->itemGroup;
        $foodCategoryBuilder = FoodCategoryBuilder::fromIikoItemGroup($iikoItemGroup);

        $this->dispatcher->dispatch(new UpdateFoodCategoryJob($foodCategoryBuilder->build()));
    }
}

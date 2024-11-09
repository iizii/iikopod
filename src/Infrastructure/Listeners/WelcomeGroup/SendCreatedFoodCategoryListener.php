<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Application\WelcomeGroup\Builders\FoodCategoryBuilder;
use Domain\Iiko\Events\ItemGroupCreatedEvent;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\CreateFoodCategoryJob;

final readonly class SendCreatedFoodCategoryListener
{
    /**
     * Create the event listener.
     */
    public function __construct(private Dispatcher $dispatcher)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ItemGroupCreatedEvent $event): void
    {
        $iikoItemGroup = $event->itemGroup;
        $foodCategoryBuilder = FoodCategoryBuilder::fromIikoItemGroup($iikoItemGroup);

        $this->dispatcher->dispatch(new CreateFoodCategoryJob($foodCategoryBuilder->build()));
    }
}

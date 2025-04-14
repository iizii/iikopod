<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Application\WelcomeGroup\Builders\FoodCategoryBuilder;
use Domain\Iiko\Events\ItemGroupUpdatedEvent;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\UpdateFoodCategoryJob;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupFoodCategory;
use Shared\Domain\ValueObjects\IntegerId;

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
        $foodCategory = WelcomeGroupFoodCategory::whereIikoMenuItemGroupId($iikoItemGroup->id->id)->first();
        $foodCategoryBuilder = FoodCategoryBuilder::fromIikoItemGroup($iikoItemGroup)
            ->setId(new IntegerId($foodCategory->id))
            ->setExternalId(new IntegerId($foodCategory->external_id));

        $this->dispatcher->dispatch(new UpdateFoodCategoryJob($foodCategoryBuilder->build()));
    }
}

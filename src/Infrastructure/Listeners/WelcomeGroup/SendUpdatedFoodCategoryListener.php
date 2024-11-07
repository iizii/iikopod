<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Iiko\Events\ProductCategoryUpdatedEvent;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\UpdateFoodCategoryJob;

final readonly class SendUpdatedFoodCategoryListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private Dispatcher $dispatcher,
        private WelcomeGroupFoodCategoryRepositoryInterface $welcomeGroupFoodCategoryRepository,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductCategoryUpdatedEvent $event): void
    {
        $foodCategory = $this->welcomeGroupFoodCategoryRepository->findByIikoProductCategoryId($event->category->id);

        if (! $foodCategory) {
            return;
        }

        $this->dispatcher->dispatch(new UpdateFoodCategoryJob(FoodCategory::withName(
            $foodCategory,
            $event->category->name
        )));
    }
}

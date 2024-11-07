<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Iiko\Events\ProductCategoryCreatedEvent;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\CreateFoodCategoryJob;
use Shared\Domain\ValueObjects\IntegerId;

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
    public function handle(ProductCategoryCreatedEvent $event): void
    {
        $iikoProductCategory = $event->category;

        $this->dispatcher->dispatch(
            new CreateFoodCategoryJob(
                new FoodCategory(
                    new IntegerId(),
                    new IntegerId(),
                    $iikoProductCategory->id,
                    $iikoProductCategory->name,
                ),
            ),
        );
    }
}

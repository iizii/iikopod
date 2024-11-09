<?php

declare(strict_types=1);

namespace Infrastructure\Listeners\WelcomeGroup;

use Domain\Iiko\Events\ItemCreatedEvent;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Bus\Dispatcher;
use Infrastructure\Jobs\WelcomeGroup\CreateFoodJob;
use Shared\Domain\ValueObjects\IntegerId;

final readonly class SendCreatedFoodListener
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
    public function handle(ItemCreatedEvent $event): void
    {
        $iikoItem = $event->item;
        $foodCategory = $this->welcomeGroupFoodCategoryRepository->findByIikoProductCategoryId($iikoItem->itemGroupId);

        if (! $foodCategory) {
            return;
        }

        $itemSize = $iikoItem->itemSizes->first();

        $food = new Food(
            new IntegerId(),
            $iikoItem->id,
            $foodCategory->id,
            new IntegerId(),
            $foodCategory->externalId,
            new IntegerId(),
            $iikoItem->name,
            $iikoItem->description,
            $itemSize->weight,
            (int) $itemSize->energy,
            $itemSize->price,
        );

        $this->dispatcher->dispatch(new CreateFoodJob($food));
    }
}

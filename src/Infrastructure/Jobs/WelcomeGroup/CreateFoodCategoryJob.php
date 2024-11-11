<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\WelcomeGroup\Builders\FoodCategoryBuilder;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;
use Infrastructure\Queue\Queue;

final class CreateFoodCategoryJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly FoodCategory $foodCategory)
    {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodCategoryRepositoryInterface $welcomeGroupFoodCategoryRepository,
    ): void {
        $response = $welcomeGroupConnector->createFoodCategory(
            new CreateFoodCategoryRequestData($this->foodCategory->name),
        );

        $foodCategoryBuilder = FoodCategoryBuilder::fromExisted($response->toDomainEntity());
        $foodCategoryBuilder = $foodCategoryBuilder->setIikoItemGroupId($this->foodCategory->iikoItemGroupId);

        $welcomeGroupFoodCategoryRepository->save($foodCategoryBuilder->build());
    }
}

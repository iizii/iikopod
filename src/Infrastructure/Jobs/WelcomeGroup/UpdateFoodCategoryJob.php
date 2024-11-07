<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;

final readonly class UpdateFoodCategoryJob implements ShouldQueue
{
    /**
     * Create a new job instance.
     */
    public function __construct(public FoodCategory $foodCategory) {}

    /**
     * Execute the job.
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodCategoryRepositoryInterface $welcomeGroupFoodCategoryRepository,
    ): void {
        $welcomeGroupConnector->updateFoodCategory(
            new CreateFoodCategoryRequestData($this->foodCategory->name),
            $this->foodCategory->externalId,
        );

        $welcomeGroupFoodCategoryRepository->update($this->foodCategory);
    }
}

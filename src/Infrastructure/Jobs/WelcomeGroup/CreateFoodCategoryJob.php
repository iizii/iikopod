<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;
use Shared\Domain\ValueObjects\IntegerId;

final readonly class CreateFoodCategoryJob implements ShouldQueue
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
        $response = $welcomeGroupConnector->createFoodCategory(
            new CreateFoodCategoryRequestData($this->foodCategory->name),
        );

        $foodCategory = new FoodCategory(
            new IntegerId(),
            new IntegerId($response->id),
            $this->foodCategory->iikoProductCategoryId,
            $this->foodCategory->name,
        );

        $welcomeGroupFoodCategoryRepository->save($foodCategory);
    }
}

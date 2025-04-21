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
        $this->delay(30);
    }

    /**
     * Execute the job.
     */
    public function handle(
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupFoodCategoryRepositoryInterface $welcomeGroupFoodCategoryRepository,
    ): void {
        try {
            $response = $welcomeGroupConnector->createFoodCategory(
                new CreateFoodCategoryRequestData($this->foodCategory->name),
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'Не удалось создать фуд категорию %s в Welcome Group, message: %s',
                    $this->foodCategory->name,
                    $e->getMessage(),
                ),
            );
        }

        $foodCategoryBuilder = FoodCategoryBuilder::fromExisted($response->toDomainEntity());
        $foodCategoryBuilder = $foodCategoryBuilder->setIikoItemGroupId($this->foodCategory->iikoItemGroupId);

        $welcomeGroupFoodCategoryRepository->save($foodCategoryBuilder->build());
    }
}

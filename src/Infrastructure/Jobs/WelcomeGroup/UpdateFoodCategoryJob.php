<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodCategoryRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;
use Infrastructure\Queue\Queue;

final class UpdateFoodCategoryJob implements ShouldBeUnique, ShouldQueue
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
            $welcomeGroupConnector->updateFoodCategory(
                new CreateFoodCategoryRequestData($this->foodCategory->name),
                $this->foodCategory->externalId,
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf(
                    'При обновлении фуд категории %s произошла ошибка %s',
                    $this->foodCategory->name,
                    $e->getMessage(),
                ),
            );
        }

        $welcomeGroupFoodCategoryRepository->update($this->foodCategory);
    }
}

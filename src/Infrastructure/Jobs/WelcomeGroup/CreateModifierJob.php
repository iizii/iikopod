<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\WelcomeGroup\Builders\ModifierBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Entities\ModifierType;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Queue\Queue;

final class CreateModifierJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Food $food,
        public readonly Item $item,
        public readonly ModifierType $modifierType,
        public readonly CreateModifierRequestData $createModifierRequestData,
    ) {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     */
    public function handle(
        QueueingDispatcher $dispatcher,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
    ): void {
        $modifierResponse = $welcomeGroupConnector->createModifier($this->createModifierRequestData);

        $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity());
        $modifierBuilder = $modifierBuilder->setInternalModifierTypeId($this->modifierType->id);

        $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
        $modifier = $modifierBuilder->setId($createdModifier->id);

        $dispatcher->dispatch(
            new CreateFoodModifierJob(
                $this->food,
                $this->item,
                $modifier->build(),
            ),
        );
    }

    /**
     * Determine number of times the job may be attempted.
     */
    public function tries(): int
    {
        return 3;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): int
    {
        return 60;
    }
}

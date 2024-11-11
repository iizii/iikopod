<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Queue\Queue;

final class CreateModifierTypeJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Food $food,
        public readonly CreateModifierTypeRequestData $createModifierTypeRequestData,
        public readonly ItemModifierGroup $modifierGroup,
    ) {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * Execute the job.
     */
    public function handle(
        QueueingDispatcher $dispatcher,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupModifierTypeRepositoryInterface $welcomeGroupModifierTypeRepository,
    ): void {
        $modifierTypeResponse = $welcomeGroupConnector->createModifierType($this->createModifierTypeRequestData);

        $modifierType = $welcomeGroupModifierTypeRepository->save($modifierTypeResponse->toDomainEntity());

        $this
            ->modifierGroup
            ->items
            ->each(function (Item $item) use ($dispatcher, $modifierType, $modifierTypeResponse): void {
                $dispatcher->dispatch(
                    new CreateModifierJob(
                        $this->food,
                        $item,
                        $modifierType,
                        new CreateModifierRequestData(
                            $item->name,
                            $modifierTypeResponse->id,
                        ),
                    ),
                );
            });
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

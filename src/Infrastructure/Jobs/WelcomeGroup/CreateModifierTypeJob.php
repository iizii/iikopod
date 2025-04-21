<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\WelcomeGroup\Builders\ModifierTypeBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifierType;
use Infrastructure\Queue\Queue;

final class CreateModifierTypeJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public $delay = 90;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Food $food,
        public readonly CreateModifierTypeRequestData $createModifierTypeRequestData,
        public readonly ItemModifierGroup $modifierGroup,
        public readonly OrganizationSetting $organizationSetting,
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
        $modifierType = WelcomeGroupModifierType::query()
            ->where('name', 'LIKE', "%{$this->createModifierTypeRequestData->name}%")
            ->first()?->toDomainEntity();

        if (! $modifierType) {
            $modifierTypeResponse = $welcomeGroupConnector->createModifierType($this->createModifierTypeRequestData);
            $modifierTypeBuilder = ModifierTypeBuilder::fromExisted($modifierTypeResponse->toDomainEntity());

            $modifierType = $welcomeGroupModifierTypeRepository->save($modifierTypeBuilder->build());
        }

        $this
            ->modifierGroup
            ->items
            ->each(function (Item $item) use ($dispatcher, $modifierType): void {
                $dispatcher->dispatch(
                    new CreateModifierJob(
                        $this->food,
                        $item,
                        $modifierType,
                        new CreateModifierRequestData(
                            $item->name,
                            (int) $modifierType->externalId->id,
                        ),
                        $this->organizationSetting
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

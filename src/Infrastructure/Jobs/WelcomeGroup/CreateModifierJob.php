<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\WelcomeGroup\Builders\ModifierBuilder;
use Application\WelcomeGroup\Builders\RestaurantModifierBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Entities\ModifierType;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupRestaurantModifierRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierRequestData;
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
        public readonly OrganizationSetting $organizationSetting,

    ) {
        $this->queue = Queue::INTEGRATIONS->value;
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(
        QueueingDispatcher $dispatcher,
        WelcomeGroupConnectorInterface $welcomeGroupConnector,
        WelcomeGroupModifierRepositoryInterface $welcomeGroupModifierRepository,
        WelcomeGroupRestaurantModifierRepositoryInterface $welcomeGroupRestaurantModifierRepository,
    ): void {
        $modifierResponse = $welcomeGroupConnector->createModifier($this->createModifierRequestData);

        $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity());
        $modifierBuilder = $modifierBuilder
            ->setInternalIikoItemId($this->item->id)
            ->setIikoExternalModifierId($this->item->externalId)
            ->setInternalModifierTypeId($this->modifierType->id);

        $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
        $modifierBuilder = $modifierBuilder->setId($createdModifier->id);
        $modifier = $modifierBuilder->build();
        $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
            new CreateRestaurantModifierRequestData(
                $this->organizationSetting->welcomeGroupRestaurantId->id,
                $createdModifier->externalId->id
            )
        );

        $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
        $restaurantModifier = $restaurantModifierBuilder
            ->setWelcomeGroupRestaurantId($this->organizationSetting->welcomeGroupRestaurantId)
            ->setRestaurantId($this->organizationSetting->id)
            ->setModifierId($modifier->id)
            ->setWelcomeGroupModifierId($createdModifier->externalId);

        $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());

        $dispatcher->dispatch(
            new CreateFoodModifierJob(
                $this->food,
                $this->item,
                $modifier,
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

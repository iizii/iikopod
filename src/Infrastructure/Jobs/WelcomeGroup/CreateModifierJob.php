<?php

declare(strict_types=1);

namespace Infrastructure\Jobs\WelcomeGroup;

use Application\WelcomeGroup\Builders\ModifierBuilder;
use Application\WelcomeGroup\Builders\RestaurantModifierBuilder;
use Domain\Iiko\Entities\Menu\Item as ModifierItem;
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
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupModifier;
use Infrastructure\Persistence\Eloquent\WelcomeGroup\Models\WelcomeGroupRestaurantModifier;
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
        public readonly ModifierItem $item,
        public readonly ModifierType $modifierType,
        public readonly CreateModifierRequestData $createModifierRequestData,
        public readonly OrganizationSetting $organizationSetting,
        public readonly string $priceCategoryId

    ) {
        $this->queue = Queue::INTEGRATIONS->value;
        $this->delay(120);
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
        $modifier = WelcomeGroupModifier::query()
            ->where('name', 'LIKE', "%{$this->createModifierRequestData->name}%")
            ->where('iiko_menu_item_modifier_item_id')
            ->first()?->toDomainEntity();

        if (! $modifier) {
            $modifierResponse = $welcomeGroupConnector->createModifier($this->createModifierRequestData);

            $modifierBuilder = ModifierBuilder::fromExisted($modifierResponse->toDomainEntity());
            $modifierBuilder = $modifierBuilder
                ->setInternalIikoItemId($this->item->id)
                ->setIikoExternalModifierId($this->item->externalId)
                ->setInternalModifierTypeId($this->modifierType->id);

            $createdModifier = $welcomeGroupModifierRepository->save($modifierBuilder->build());
            $modifierBuilder = $modifierBuilder->setId($createdModifier->id);
            $modifier = $modifierBuilder->build();
        }

        $restaurantModifier = WelcomeGroupRestaurantModifier::query()
            ->where('welcome_group_restaurant_id', $this->organizationSetting->id)
            ->where('welcome_group_modifier_id', $modifier->id)
            ->first()?->toDomainEntity();

        if (! $restaurantModifier) {
            $restaurantModifierResponse = $welcomeGroupConnector->createRestaurantModifier(
                new CreateRestaurantModifierRequestData(
                    (int) $this->organizationSetting->welcomeGroupRestaurantId->id,
                    (int) $modifier->externalId->id
                )
            );

            $restaurantModifierBuilder = RestaurantModifierBuilder::fromExisted($restaurantModifierResponse->toDomainEntity());
            $restaurantModifier = $restaurantModifierBuilder
                ->setWelcomeGroupRestaurantId($this->organizationSetting->id)
                ->setWelcomeGroupModifierId($modifier->id);

            $welcomeGroupRestaurantModifierRepository->save($restaurantModifier->build());
        }

        $dispatcher->dispatch(
            new CreateFoodModifierJob(
                $this->food,
                $this->item, // айтем модификатора (в случае с айкой схож по структуре с обычным айтемом (блюдом)
                $modifier,
                $this->priceCategoryId
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

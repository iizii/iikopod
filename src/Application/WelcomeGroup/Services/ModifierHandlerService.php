<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Services;

use Domain\Iiko\Entities\Menu\ItemModifierGroup;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierRepositoryInterface;
use Domain\WelcomeGroup\Repositories\WelcomeGroupModifierTypeRepositoryInterface;

final readonly class ModifierHandlerService
{
    public function __construct(
        private readonly WelcomeGroupConnectorInterface $connector,
        private readonly WelcomeGroupModifierTypeRepositoryInterface $modifierTypeRepository,
        private readonly WelcomeGroupModifierRepositoryInterface $modifierRepository
    ) {}

    public function handleModifierGroups(Food $food, iterable $modifierGroups): void
    {
        foreach ($modifierGroups as $modifierGroup) {
            $this->processModifierGroup($food, $modifierGroup);
        }
    }

    private function processModifierGroup(Food $food, ItemModifierGroup $modifierGroup): void
    {
        // Логика обработки модификаторов
        $maxQuantity = $modifierGroup->maxQuantity;

        $modifiers = $this->modifierRepository->findByGroupId($modifierGroup->id);
        $difference = count($modifiers) - $maxQuantity;

        if ($difference > 0) {
            $this->removeExcessModifiers($modifiers, $difference);
        } elseif ($difference < 0) {
            $this->createMissingModifiers($food, $modifierGroup, abs($difference));
        }

        $this->updateModifiers($modifiers, $modifierGroup);
    }

    private function removeExcessModifiers(array $modifiers, int $count): void
    {
        $modifiersToDelete = array_slice($modifiers, -$count);
        foreach ($modifiersToDelete as $modifier) {
            $this->connector->deleteModifier($modifier->externalId);
            $this->modifierRepository->delete($modifier);
        }
    }

    private function createMissingModifiers(Food $food, ItemModifierGroup $modifierGroup, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $response = $this->connector->createModifierType($modifierGroup);
            $this->modifierRepository->save($response->toDomainEntity());
        }
    }

    private function updateModifiers(array $modifiers, ItemModifierGroup $modifierGroup): void
    {
        foreach ($modifiers as $modifier) {
            $response = $this->connector->updateModifier($modifier->externalId, $modifierGroup);
            $this->modifierRepository->save($response->toDomainEntity());
        }
    }
}

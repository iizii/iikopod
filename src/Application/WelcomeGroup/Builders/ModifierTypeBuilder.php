<?php

declare(strict_types=1);

namespace Application\WelcomeGroup\Builders;

use Domain\WelcomeGroup\Entities\ModifierType;
use Domain\WelcomeGroup\Enums\ModifierTypeBehaviour;
use Shared\Domain\ValueObjects\IntegerId;

final class ModifierTypeBuilder
{
    public function __construct(
        private IntegerId $id,
        private IntegerId $externalId,
        private IntegerId $iikoMenuItemModifierGroupId,
        private string $name,
        private ModifierTypeBehaviour $behaviour,
    ) {}

    public static function fromExisted(ModifierType $modifierType): self
    {
        return new self(
            $modifierType->id,
            $modifierType->externalId,
            $modifierType->iikoMenuItemModifierGroupId,
            $modifierType->name,
            $modifierType->behaviour
        );
    }

    public function setId(IntegerId $id): ModifierTypeBuilder
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

//    public function setIikoMenuItemModifierGroupId(IntegerId $iikoMenuItemModifierGroupId): ModifierTypeBuilder
//    {
//        $clone = clone $this;
//        $clone->iikoMenuItemModifierGroupId = $iikoMenuItemModifierGroupId;
//
//        return $clone;
//    }

    public function setExternalId(IntegerId $externalId): ModifierTypeBuilder
    {
        $clone = clone $this;
        $clone->externalId = $externalId;

        return $clone;
    }

    public function setName(string $name): ModifierTypeBuilder
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function setBehaviour(ModifierTypeBehaviour $behaviour): ModifierTypeBuilder
    {
        $clone = clone $this;
        $clone->behaviour = $behaviour;

        return $clone;
    }

    public function build(): ModifierType
    {
        return new ModifierType(
            $this->id,
            $this->externalId,
            $this->iikoMenuItemModifierGroupId,
            $this->name,
            $this->behaviour,
        );
    }
}

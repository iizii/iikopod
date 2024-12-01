<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Item extends DomainEntity
{
    /**
     * @param  ItemModifierCollection<array-key, Modifier>  $modifiers
     */
    public function __construct(
        public readonly IntegerId $itemId,
        public readonly int $price,
        public readonly int $discount,
        public readonly int $amount,
        public readonly ?string $comment,
        public readonly ItemModifierCollection $modifiers,
    ) {}

    public function addModifier(Modifier $modifier): self
    {
        if (! $this->modifiers->contains($modifier)) {
            $this->modifiers->add($modifier);
        }

        return $this;
    }
}

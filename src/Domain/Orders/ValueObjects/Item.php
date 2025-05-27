<?php

declare(strict_types=1);

namespace Domain\Orders\ValueObjects;

use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Item extends DomainEntity
{
    /**
     * @param  ItemModifierCollection<array-key, Modifier>  $modifiers
     */
    public function __construct(
        public IntegerId $itemId, // id iiko item в нашей внутренней системе
        public readonly int $price,
        public readonly int $discount,
        public readonly int $amount,
        public readonly ?string $comment,
        public readonly ItemModifierCollection $modifiers,
        public readonly ?IntegerId $welcomeGroupExternalId = null, // отсутствует по-умолчанию, ибо при получении данных из IIKO мы его не знаем и нам требуется сверяться с БД по другим полям (например positionId)
        public readonly ?StringId $positionId = null, // id позиции в iiko(не продукта) (внешний идентификатор)
        public readonly ?IntegerId $welcomeGroupExternalFoodId = null // // отсутствует по-умолчанию, ибо при получении данных из IIKO мы его не знаем и нам требуется сверяться с БД по другим полям
    ) {}

    public function addModifier(Modifier $modifier): self
    {
        if (! $this->modifiers->contains($modifier)) {
            $this->modifiers->add($modifier);
        }

        return $this;
    }
}

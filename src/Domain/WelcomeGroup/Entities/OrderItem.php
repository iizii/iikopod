<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Domain\WelcomeGroup\ValueObjects\Order\FoodModifierCollection;
use Domain\WelcomeGroup\ValueObjects\Order\FoodModifierIdCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class OrderItem extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $orderId,
        public readonly IntegerId $id,
        public readonly IntegerId $foodId,
        public readonly Food $food,
        public readonly string $statusComment,
        public readonly string $status,
        public readonly string $comment,
        public readonly float $price,
        public readonly float $discount,
        public readonly float $sum,
        public readonly bool $isInternetPayment,
        public readonly FoodModifierIdCollection $foodModifiers,
        public readonly FoodModifierCollection $foodModifiersArray,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt,
    ) {}
}

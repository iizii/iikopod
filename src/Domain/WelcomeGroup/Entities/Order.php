<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Domain\WelcomeGroup\Enums\OrderStatus;
use Domain\WelcomeGroup\Enums\OrderStatusProcessRouting;
use Domain\WelcomeGroup\ValueObjects\Order\OrderItemCollection;
use Domain\WelcomeGroup\ValueObjects\Order\OrderPrices;
use Domain\WelcomeGroup\ValueObjects\Order\PromotionIdCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;

final class Order extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly IntegerId $restaurantId,
        public readonly IntegerId $clientId,
        public readonly IntegerId $phoneId,
        public readonly IntegerId $addressId,
        public readonly IntegerId $coupon,
        public readonly IntegerId $driver,
        public readonly IntegerId $driverChoiceAlgorithm,
        public readonly OrderStatus $status,
        public readonly OrderStatusProcessRouting $statusProcessRouting,
        public readonly OrderPrices $prices,
        public readonly PromotionIdCollection $promotions,
        public readonly OrderItemCollection $orderItems,
        public readonly string $number,
        public readonly string $statusComment,
        public readonly string $comment,
        public readonly int $duration,
        public readonly string $start,
        public readonly bool $isInternetPayment,
        public readonly bool $isRzdPayment,
        public readonly bool $isBankAccountPayment,
        public readonly bool $isPreorder,
        public readonly string $commentWhyDriver,
        public readonly int $km,
        public readonly int $durations,
        public readonly \DateTimeInterface $awaitingCooking,
        public readonly \DateTimeInterface $awaitingDelivery,
        public readonly int $timeProduction,
        public readonly bool $lateness,
        public readonly CouponLateness $couponLateness,
        public readonly int $productionTime,
        public readonly int $timeDelivery,
        public readonly string $source,
        public readonly int $locked,
        public readonly int $producing,
        public readonly int $completed,
        public readonly int $delivering,
        public readonly int $delivered,
        public readonly int $cancelled,
        public readonly int $rejected,
        public readonly int $finished,
        public readonly string $timePreorder,
        public readonly int $estimatedTimeDelivery,
        public readonly string $statusAutoDriverAssignments,
        public readonly string $autoCause,
        public readonly string $timeStatusAutoAssigned,
        public readonly int $effective,
        public readonly int $ineffective,
        public readonly int $driverIncome,
        public readonly int $tariff,
        public readonly int $timeWaitingCooking,
        public readonly int $timeCooking,
        public readonly bool $offline,
        public readonly bool $flagMsvrp,
        public readonly string $typeProduction,
        public readonly string $statusApprove,
        public readonly string $approveFailReason,
        public readonly string $approveBy,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt,
    ) {}
}

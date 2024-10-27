<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\Entities;

use Domain\WelcomeGroup\Enums\RestaurantStatus;
use Domain\WelcomeGroup\ValueObjects\Restaurant\AreaCollection;
use Domain\WelcomeGroup\ValueObjects\Restaurant\DriverIdCollection;
use Domain\WelcomeGroup\ValueObjects\Restaurant\Emergency;
use Domain\WelcomeGroup\ValueObjects\Restaurant\Printer;
use Domain\WelcomeGroup\ValueObjects\Restaurant\PrinterPos;
use Domain\WelcomeGroup\ValueObjects\Restaurant\WorkshopIdCollection;
use Shared\Domain\DomainEntity;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;

final class Restaurant extends DomainEntity
{
    public function __construct(
        public readonly IntegerId $id,
        public readonly StringId $externalId,
        public readonly RestaurantStatus $status,
        public readonly int $business,
        public readonly WorkshopIdCollection $workshops,
        public readonly DriverIdCollection $drivers,
        public readonly string $name,
        public readonly string $statusComment,
        public readonly string $supplierName,
        public readonly string $supplierInn,
        public readonly string $supplierPhone,
        public readonly string $description,
        public readonly int $latitude,
        public readonly int $longitude,
        public readonly Printer $printer,
        public readonly PrinterPos $printerPos,
        public readonly AreaCollection $area,
        public readonly int $legalPerson,
        public readonly int $brand,
        public readonly int $timeWaitingWeekday,
        public readonly int $timeWaitingWeekend,
        public readonly int $timeCookingWeekday,
        public readonly int $timeCookingWeekend,
        public readonly int $timeWaitingDelivering,
        public readonly int $timeWaitingCooking,
        public readonly int $timeCooking,
        public readonly int $timeDelivering,
        public readonly int $timezone,
        public readonly bool $isCoupon,
        public readonly int $couponType,
        public readonly string $address,
        public readonly bool $isAnv,
        public readonly int $typeRouting,
        public readonly string $coefficientRouting,
        public readonly int $typeProduction,
        public readonly int $timeCouponAvailability,
        public readonly int $activeOrderCount,
        public readonly Emergency $emergency,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt,
    ) {}
}

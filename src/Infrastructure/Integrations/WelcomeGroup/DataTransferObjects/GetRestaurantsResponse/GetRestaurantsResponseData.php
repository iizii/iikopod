<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantsResponse;

use Carbon\CarbonImmutable;
use Domain\WelcomeGroup\Entities\Restaurant;
use Domain\WelcomeGroup\Enums\RestaurantStatus;
use Domain\WelcomeGroup\ValueObjects\Restaurant\Area;
use Domain\WelcomeGroup\ValueObjects\Restaurant\AreaCollection;
use Domain\WelcomeGroup\ValueObjects\Restaurant\DriverIdCollection;
use Domain\WelcomeGroup\ValueObjects\Restaurant\WorkshopIdCollection;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;

final class GetRestaurantsResponseData extends ResponseData
{
    /**
     * @param  array<array-key, int>  $workshops
     * @param  array<array-key, int>  $drivers
     * @param  array<array<int>>  $area
     */
    public function __construct(
        public readonly int $business,
        public readonly array $workshops,
        public readonly array $drivers,
        public readonly string $name,
        public readonly string $statusComment,
        public readonly string $supplierName,
        public readonly string $supplierInn,
        public readonly string $supplierPhone,
        public readonly string $description,
        public readonly int $latitude,
        public readonly int $longitude,
        public readonly string $externalId,
        public readonly GetRestaurantsResponsePrinterData $printer,
        public readonly array $area,
        public readonly int $legalPerson,
        public readonly int $brand,
        public readonly GetRestaurantsResponsePrinterPosData $printerPos,
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
        public readonly int $id,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
        public readonly RestaurantStatus $status,
        public readonly int $activeOrderCount,
        public readonly GetRestaurantsResponseEmergencyData $emergency,
    ) {}

    public function toDomainEntity(): Restaurant
    {
        return new Restaurant(
            new IntegerId($this->id),
            new StringId($this->externalId),
            $this->status,
            $this->business,
            new WorkshopIdCollection(
                array_map(
                    static fn (int $id): IntegerId => new IntegerId($id),
                    $this->workshops,
                ),
            ),
            new DriverIdCollection(
                array_map(
                    static fn (int $id): IntegerId => new IntegerId($id),
                    $this->drivers,
                ),
            ),
            $this->name,
            $this->statusComment,
            $this->supplierName,
            $this->supplierInn,
            $this->supplierPhone,
            $this->description,
            $this->latitude,
            $this->longitude,
            $this->printer->toDomainEntity(),
            $this->printerPos->toDomainEntity(),
            new AreaCollection(
                array_map(
                    static fn (array $data): Area => new Area($data[0], $data[1]),
                    $this->area,
                ),
            ),
            $this->legalPerson,
            $this->brand,
            $this->timeWaitingWeekday,
            $this->timeWaitingWeekend,
            $this->timeCookingWeekday,
            $this->timeCookingWeekend,
            $this->timeWaitingDelivering,
            $this->timeWaitingCooking,
            $this->timeCooking,
            $this->timeDelivering,
            $this->timezone,
            $this->isCoupon,
            $this->couponType,
            $this->address,
            $this->isAnv,
            $this->typeRouting,
            $this->coefficientRouting,
            $this->typeProduction,
            $this->timeCouponAvailability,
            $this->activeOrderCount,
            $this->emergency->toDomainEntity(),
            $this->created,
            $this->updated,
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order;

use Carbon\CarbonImmutable;
use Domain\Orders\Enums\OrderSource;
use Domain\Orders\ValueObjects\Payment;
use Domain\WelcomeGroup\Entities\Order;
use Domain\WelcomeGroup\Enums\OrderStatus;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems\OrderItems;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class GetOrdersByRestaurantResponseData extends ResponseData
{
    /**
     * @param  int[]  $promotions
     * @param  OrderItems[]  $orderItems
     */
    public function __construct(
        public readonly ?string $statusComment,
        public readonly string $status,
        public readonly string $number,
        public readonly ?CarbonImmutable $start,
        public readonly int $duration,
        public readonly float $price,
        public readonly float $sum,
        public readonly int $discount,
        public readonly int $restaurant,
        public readonly int $client,
        public readonly int $phone,
        public readonly ?int $address,
        public readonly array $promotions,
        public readonly ?int $coupon,
        public readonly ?int $driver,
        public readonly ?string $comment,
        public readonly ?bool $isInternetPayment,
        public readonly ?bool $isRZDPayment, // обрабатывается криво из-за нейминга в ПОД, но в целом мы не юзаем это
        public readonly ?bool $isBankAccountPayment,
        public readonly ?bool $isPreorder,
        public readonly ?int $driverChoiceAlgorithm,
        public readonly ?string $commentWhyDriver,
        public readonly ?CarbonImmutable $estimatedDeliveryTime,
        public readonly int $km,
        public readonly int $durations,
        public readonly ?CarbonImmutable $awaitingCooking,
        public readonly ?CarbonImmutable $awaitingDelivery,
        public readonly ?int $timeProduction,
        public readonly bool $lateness,
        public readonly ?int $couponLateness,
        public readonly ?string $couponLatenessCode,
        public readonly ?int $productionTime,
        public readonly ?int $timeDelivery,
        public readonly ?string $statusProcessRouting,
        public readonly ?string $source,
        public readonly int $id,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
        public readonly ?CarbonImmutable $locked, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $producing, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $completed, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $delivering, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $delivered, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $cancelled, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $rejected,
        public readonly ?CarbonImmutable $finished, // не соответствует доке, должен быть int
        public readonly ?CarbonImmutable $timePreorder,
        public readonly int $estimatedTimeDelivery,
        public readonly string $statusAutoDriverAssignments,
        public readonly ?string $autoCause,
        public readonly ?CarbonImmutable $timeStatusAutoAssigned,
        public readonly int $effective,
        public readonly int $ineffective,
        public readonly int $driverIncome,
        public readonly ?int $tariff,
        public readonly ?int $timeWaitingCooking,
        public readonly ?int $timeCooking,
        public readonly ?bool $offline,
        public readonly bool $flagMsvrp,
        public readonly ?string $typeProduction,
        public readonly string $statusApprove,
        public readonly ?string $approveFailReason,
        public readonly ?string $approveBy,
        public readonly ?array $orderItems
    ) {}

//    public function toDomainEntity(): Order
//    {
//        return new Order(
//            new IntegerId($this->id),
//            new IntegerId($this->restaurant),
//            new IntegerId($this->client),
//            new IntegerId($this->phone),
//            new IntegerId($this->address),
//            OrderStatus::from($this->status),
//            $this->discount,
//            $this->comment,
//            $this->isInternetPayment,
//            $this->isRZDPayment,
//            $this->isBankAccountPayment,
//            $this->isPreorder,
//            $this->km,
//            $this->durations,
//            $this->awaitingCooking,
//            $this->awaitingDelivery,
//            $this->timeProduction,
//            $this->lateness,
//            $this->couponLateness,
//            $this->productionTime,
//            $this->timeDelivery,
//            $this->statusProcessRouting,
//            $this->source,
//        );
//    }

    public function toDomainEntity(): \Domain\Orders\Entities\Order
    {
        $payment = null;

        new Payment()

        return new \Domain\Orders\Entities\Order(
            new IntegerId(),
            new IntegerId(),
            OrderSource::WELCOME_GROUP,
            \Domain\Orders\Enums\OrderStatus::from($this->status),
            new StringId(),
            new IntegerId($this->id),
            $this->comment,



        );
    }
}

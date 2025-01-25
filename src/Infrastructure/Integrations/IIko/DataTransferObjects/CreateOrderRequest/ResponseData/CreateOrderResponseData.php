<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\ResponseData;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateOrderResponseData extends ResponseData
{
    public function __construct(
        public readonly string $correlationId,
        public readonly OrderInfo $orderInfo
    ) {}
}
//TODO: нужно распилить класс на мн-во, торопился поэтому всё в одном
final class OrderInfo extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $posId,
        public readonly string $externalNumber,
        public readonly string $organizationId,
        public readonly int $timestamp,
        public readonly string $creationStatus,
        public readonly ErrorInfo $errorInfo,
        public readonly Order $order
    ) {}
}

final class ErrorInfo extends ResponseData
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly string $description,
        public readonly ?array $additionalData // null или массив
    ) {}
}

final class Order extends ResponseData
{
    /**
     * @param  Item[]  $items
     * @param  Combo[]  $combos
     * @param  Payment[]  $payments
     * @param  Tip[]  $tips
     * @param  Discount[]  $discounts
     * @param  ExternalData[]  $externalData
     */
    public function __construct(
        public readonly string $parentDeliveryId,
        public readonly Customer $customer,
        public readonly string $phone,
        public readonly DeliveryPoint $deliveryPoint,
        public readonly string $status,
        public readonly CancelInfo $cancelInfo,
        public readonly CourierInfo $courierInfo,
        public readonly string $completeBefore,
        public readonly string $whenCreated,
        public readonly string $whenConfirmed,
        public readonly string $whenPrinted,
        public readonly string $whenCookingCompleted,
        public readonly string $whenSended,
        public readonly string $whenDelivered,
        public readonly string $comment,
        public readonly Problem $problem,
        public readonly Operator $operator,
        public readonly MarketingSource $marketingSource,
        public readonly int $deliveryDuration,
        public readonly int $indexInCourierRoute,
        public readonly string $cookingStartTime,
        public readonly bool $isDeleted,
        public readonly string $whenReceivedByApi,
        public readonly string $whenReceivedFromFront,
        public readonly string $movedFromDeliveryId,
        public readonly string $movedFromTerminalGroupId,
        public readonly string $movedFromOrganizationId,
        public readonly ExternalCourierService $externalCourierService,
        public readonly string $movedToDeliveryId,
        public readonly string $movedToTerminalGroupId,
        public readonly string $movedToOrganizationId,
        public readonly string $menuId,
        public readonly string $deliveryZone,
        public readonly string $estimatedTime,
        public readonly bool $isAsap,
        public readonly string $whenPacked,
        public readonly int $sum,
        public readonly int $number,
        public readonly string $sourceKey,
        public readonly string $whenBillPrinted,
        public readonly string $whenClosed,
        public readonly Conception $conception,
        public readonly GuestsInfo $guestsInfo,
        public readonly array $items,
        public readonly array $combos,
        public readonly array $payments,
        public readonly array $tips,
        public readonly array $discounts,
        public readonly OrderType $orderType,
        public readonly string $terminalGroupId,
        public readonly int $processedPaymentsSum,
        public readonly LoyaltyInfo $loyaltyInfo,
        public readonly array $externalData
    ) {}
}

final class Customer extends ResponseData
{
    public function __construct(public readonly string $type) {}
}

final class DeliveryPoint extends ResponseData
{
    public function __construct(
        public readonly Coordinates $coordinates,
        public readonly Address $address,
        public readonly string $externalCartographyId,
        public readonly string $comment
    ) {}
}

final class Coordinates extends ResponseData
{
    public function __construct(public readonly float $latitude, public readonly float $longitude) {}
}

final class Address extends ResponseData
{
    public function __construct(
        public readonly Street $street,
        public readonly string $index,
        public readonly string $house,
        public readonly string $building,
        public readonly string $flat,
        public readonly string $entrance,
        public readonly string $floor,
        public readonly string $doorphone,
        public readonly Region $region,
        public readonly ?string $line1
    ) {}
}

final class Street extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly City $city
    ) {}
}

final class City extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class Region extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class CancelInfo extends ResponseData
{
    public function __construct(
        public readonly string $whenCancelled,
        public readonly Cause $cause,
        public readonly string $comment
    ) {}
}

final class Cause extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class CourierInfo extends ResponseData
{
    public function __construct(
        public readonly Courier $courier,
        public readonly bool $isCourierSelectedManually
    ) {}
}

final class Courier extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $phone
    ) {}
}

final class Problem extends ResponseData
{
    public function __construct(public readonly bool $hasProblem, public readonly string $description) {}
}

final class Operator extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $phone
    ) {}
}

final class MarketingSource extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class ExternalCourierService extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class Conception extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $code
    ) {}
}

final class GuestsInfo extends ResponseData
{
    public function __construct(public readonly int $count, public readonly bool $splitBetweenPersons) {}
}

final class Item extends ResponseData
{
    public function __construct(
        public readonly string $type,
        public readonly string $status,
        public readonly Deleted $deleted,
        public readonly int $amount,
        public readonly string $comment,
        public readonly string $whenPrinted,
        public readonly Size $size,
        public readonly ComboInformation $comboInformation
    ) {}
}

final class Deleted extends ResponseData
{
    public function __construct(public readonly DeletionMethod $deletionMethod) {}
}

final class DeletionMethod extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $comment,
        public readonly RemovalType $removalType
    ) {}
}

final class RemovalType extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class ComboInformation extends ResponseData
{
    public function __construct(
        public readonly string $comboId,
        public readonly string $comboSourceId,
        public readonly string $groupId,
        public readonly string $groupName
    ) {}
}

final class Combo extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $amount,
        public readonly float $price,
        public readonly string $sourceId,
        public readonly Size $size
    ) {}
}

final class Payment extends ResponseData
{
    public function __construct(
        public readonly PaymentType $paymentType,
        public readonly float $sum,
        public readonly bool $isPreliminary,
        public readonly bool $isExternal,
        public readonly bool $isProcessedExternally,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay
    ) {}
}

final class PaymentType extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $kind
    ) {}
}

final class Tip extends ResponseData
{
    public function __construct(
        public readonly TipsType $tipsType,
        public readonly PaymentType $paymentType,
        public readonly float $sum,
        public readonly bool $isPreliminary,
        public readonly bool $isExternal,
        public readonly bool $isProcessedExternally,
        public readonly bool $isFiscalizedExternally,
        public readonly bool $isPrepay
    ) {}
}

final class TipsType extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class Discount extends ResponseData
{
    /**
     * @param  string[]  $selectivePositions
     * @param  SelectivePositionWithSum[]  $selectivePositionsWithSum
     */
    public function __construct(
        public readonly DiscountType $discountType,
        public readonly float $sum,
        public readonly array $selectivePositions,
        public readonly array $selectivePositionsWithSum
    ) {}
}

final class DiscountType extends ResponseData
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

final class SelectivePositionWithSum extends ResponseData
{
    public function __construct(public readonly string $positionId, public readonly float $sum) {}
}

final class ExternalData extends ResponseData
{
    public function __construct(public readonly string $key, public readonly string $value) {}
}

final class OrderType extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $orderServiceType
    ) {}
}

final class LoyaltyInfo extends ResponseData
{
    /**
     * @param  string[]  $appliedManualConditions
     */
    public function __construct(
        public readonly string $coupon,
        public readonly array $appliedManualConditions
    ) {}
}

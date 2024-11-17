<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class Order extends Data
{
    /**
     * @param  DataCollection<array-key, Items>  $items
     * @param  DataCollection<array-key, Combos>  $combos
     * @param  DataCollection<array-key, Payments>  $payments
     * @param  DataCollection<array-key, Tips>  $tips
     * @param  DataCollection<array-key, Discounts>  $discounts
     */
    public function __construct(
        public readonly ?string $parentDeliveryId,
        public readonly ?Customer $customer,
        public readonly string $phone,
        public readonly ?DeliveryPoint $deliveryPoint,
        public readonly string $status,
        public readonly ?CancelInfo $cancelInfo,
        public readonly ?CourierInfo $courierInfo,
        public readonly ?string $completeBefore,
        public readonly ?string $whenCreated,
        public readonly ?string $whenConfirmed,
        public readonly ?string $whenPrinted,
        public readonly ?string $whenCookingCompleted,
        public readonly ?string $whenSended,
        public readonly ?string $whenDelivered,
        public readonly string $comment,
        public readonly ?Problem $problem,
        public readonly ?Operator $operator,
        public readonly ?MarketingSource $marketingSource,
        public readonly int $deliveryDuration,
        public readonly ?int $indexInCourierRoute,
        public readonly string $cookingStartTime,
        public readonly bool $isDeleted,
        public readonly string $whenReceivedByApi,
        public readonly string $whenReceivedFromFront,
        public readonly ?string $movedFromDeliveryId,
        public readonly ?string $movedFromTerminalGroupId,
        public readonly ?string $movedFromOrganizationId,
        public readonly ?ExternalCourierService $externalCourierService,
        public readonly ?string $movedToDeliveryId,
        public readonly ?string $movedToTerminalGroupId,
        public readonly ?string $movedToOrganizationId,
        public readonly ?string $menuId,
        public readonly ?string $deliveryZone,
        public readonly ?string $estimatedTime,
        public readonly ?string $whenPacked,
        public readonly int $sum,
        public readonly int $number,
        public readonly string $sourceKey,
        public readonly ?string $whenBillPrinted,
        public readonly ?string $whenClosed,
        public readonly ?Conception $conception,
        public readonly GuestsInfo $guestsInfo,
        #[DataCollectionOf(Items::class)]
        public readonly DataCollection $items,
        #[DataCollectionOf(Combos::class)]
        public readonly DataCollection $combos,
        #[DataCollectionOf(Payments::class)]
        public readonly DataCollection $payments,
        #[DataCollectionOf(Tips::class)]
        public readonly DataCollection $tips,
        #[DataCollectionOf(Discounts::class)]
        public readonly DataCollection $discounts,
        public readonly OrderType $orderType,
        public readonly string $terminalGroupId,
        public readonly int $processedPaymentsSum,
        public readonly LoyaltyInfo $loyaltyInfo,
    ) {}
}

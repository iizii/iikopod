<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Domain\Iiko\Entities\Order\OrderSettings;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Combos;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\LoyaltyInfo;
use Shared\Infrastructure\Integrations\ResponseData;

final class Order extends ResponseData
{
    /**
     * @param  Items[]  $items
//     * @param  Combos[]  $combos
     * @param  Payments[]  $payments
     * @param  Tips[]  $tips
     * @param  ExternalData[]  $externalData
     */
    public function __construct(
        public readonly null $menuId,
        public readonly string $id,
        public readonly string $externalNumber,
        public readonly string $completeBefore,
        public readonly string $phone,
        public readonly string $orderTypeId,
        public readonly ?string $orderServiceType,
        public readonly DeliveryPoint $deliveryPoint,
        public readonly string $comment,
        public readonly Customer $customer,
//        public readonly Guests $guests,
//        public readonly string $marketingSourceId,
//        public readonly string $operatorId,
//        public readonly int $deliveryDuration,
//        public readonly string $deliveryZone,
        public readonly array $items,
//        public readonly array $combos,
        public readonly array $payments,
        public readonly ?array $tips,
//        public readonly string $sourceKey,
//        public readonly DiscountsInfo $discountsInfo,
//        public readonly LoyaltyInfo $loyaltyInfo,
//        public readonly ChequeAdditionalInfo $chequeAdditionalInfo,
        public readonly ?array $externalData
    ) {}

    //    public function toDomainEntity(): OrderSettings
    //    {
    //        return new OrderSettings(
    //            $this->transportToFrontTimeout,
    //            $this->checkStopList,
    //        );
    //    }
}

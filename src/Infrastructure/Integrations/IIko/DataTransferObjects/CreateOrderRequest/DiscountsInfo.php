<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Domain\Iiko\Entities\Order\OrderSettings;
use Shared\Infrastructure\Integrations\ResponseData;

final class DiscountsInfo extends ResponseData
{
    /**
     * @param  Discounts[]  $discounts
     */
    public function __construct(public readonly Card $card, public readonly array $discounts) {}

    //    public function toDomainEntity(): OrderSettings
    //    {
    //        //        return new OrderSettings(
    //        //            $this->transportToFrontTimeout,
    //        //            $this->checkStopList,
    //        //        );
    //    }
}

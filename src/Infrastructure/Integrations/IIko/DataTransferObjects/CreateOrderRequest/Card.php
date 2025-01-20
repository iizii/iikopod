<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Domain\Iiko\Entities\Order\OrderSettings;
use Shared\Infrastructure\Integrations\ResponseData;

final class Card extends ResponseData
{
    public function __construct(public readonly string $track) {}

    //    public function toDomainEntity(): OrderSettings
    //    {
    //        //        return new OrderSettings(
    //        //            $this->transportToFrontTimeout,
    //        //            $this->checkStopList,
    //        //        );
    //    }
}

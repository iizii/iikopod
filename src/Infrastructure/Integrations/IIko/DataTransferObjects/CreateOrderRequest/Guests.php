<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Domain\Iiko\Entities\Order\OrderSettings;
use Shared\Infrastructure\Integrations\ResponseData;

final class Guests extends ResponseData
{
    public function __construct(public readonly int $count, public readonly bool $splitBetweenPersons) {}

    //    public function toDomainEntity(): OrderSettings
    //    {
    //        //        return new OrderSettings(
    //        //            $this->transportToFrontTimeout,
    //        //            $this->checkStopList,
    //        //        );
    //    }
}

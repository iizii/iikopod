<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class CourierInfo extends Data
{
    public function __construct(public readonly Courier $courier, public readonly bool $isCourierSelectedManually) {}
}

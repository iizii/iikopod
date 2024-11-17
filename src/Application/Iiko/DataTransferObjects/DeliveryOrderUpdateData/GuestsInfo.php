<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class GuestsInfo extends Data
{
    public function __construct(public readonly int $count, public readonly bool $splitBetweenPersons) {}
}

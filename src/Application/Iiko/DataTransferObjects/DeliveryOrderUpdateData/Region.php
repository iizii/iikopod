<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Region extends Data
{
    public function __construct(public readonly string $id, public readonly string $name) {}
}

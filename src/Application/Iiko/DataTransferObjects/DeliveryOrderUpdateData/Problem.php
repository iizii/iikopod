<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Problem extends Data
{
    public function __construct(public readonly bool $hasProblem, public readonly string $description) {}
}

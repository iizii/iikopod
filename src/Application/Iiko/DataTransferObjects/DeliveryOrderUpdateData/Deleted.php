<?php

declare(strict_types=1);

namespace Application\Iiko\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Deleted extends Data
{
    public function __construct(public readonly DeletionMethod $deletionMethod) {}
}

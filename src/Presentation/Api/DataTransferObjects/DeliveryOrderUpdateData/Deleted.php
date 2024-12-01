<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class Deleted extends Data
{
    public function __construct(public readonly DeletionMethod $deletionMethod) {}
}

<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Data;

final class ComboInformation extends Data
{
    public function __construct(
        public readonly string $comboId,
        public readonly string $comboSourceId,
        public readonly string $groupId,
        public readonly string $groupName
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Region;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Street;
use Spatie\LaravelData\Data;

final class Address extends Data
{
    public function __construct(
        public readonly ?string $line1,
        public readonly string $flat,
        public readonly string $entrance,
        public readonly string $floor,
        public readonly ?Street $street,
        public readonly ?string $index,
        public readonly ?string $house,
        public readonly ?Region $region,
    ) {}
}

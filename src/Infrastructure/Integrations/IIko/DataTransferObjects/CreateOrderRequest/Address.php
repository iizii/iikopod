<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Region;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\Street;
use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\StreetTwo;
use Spatie\LaravelData\Data;

final class Address extends Data
{
    public function __construct(
        //        public readonly ?string $line1,
        public readonly ?string $flat,
        public readonly ?string $entrance,
        public readonly ?string $floor,
        public readonly ?StreetTwo $street,
        public readonly ?string $index,
        public readonly ?string $house,
        //        public readonly ?Region $region,
        public readonly ?string $regionId,
        public readonly ?string $building,
        public readonly ?string $doorphone = null,
        public readonly string $type = 'legacy',
    ) {}
}

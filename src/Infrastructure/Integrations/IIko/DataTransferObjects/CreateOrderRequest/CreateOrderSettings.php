<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Domain\Iiko\Entities\Order\OrderSettings;
use Shared\Infrastructure\Integrations\ResponseData;

final class CreateOrderSettings extends ResponseData
{
    public function __construct(
        public readonly int $transportToFrontTimeout = 0,
        public readonly bool $checkStopList = false,
    ) {}

    public function toDomainEntity(): OrderSettings
    {
        return new OrderSettings(
            $this->transportToFrontTimeout,
            $this->checkStopList,
        );
    }
}

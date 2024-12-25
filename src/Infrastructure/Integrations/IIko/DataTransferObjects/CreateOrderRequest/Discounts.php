<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Shared\Infrastructure\Integrations\ResponseData;

final class Discounts extends ResponseData
{
    public function __construct(public readonly string $type) {}
}

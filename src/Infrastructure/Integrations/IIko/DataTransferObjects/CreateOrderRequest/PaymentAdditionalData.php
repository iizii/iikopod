<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Spatie\LaravelData\Data;

final class PaymentAdditionalData extends Data
{
    public function __construct(public readonly string $type) {}
}

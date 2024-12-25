<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Shared\Infrastructure\Integrations\ResponseData;

final class ChequeAdditionalInfo extends ResponseData
{
    public function __construct(
        public readonly bool $needReceipt,
        public readonly string $email,
        public readonly string $settlementPlace,
        public readonly string $phone
    ) {}
}

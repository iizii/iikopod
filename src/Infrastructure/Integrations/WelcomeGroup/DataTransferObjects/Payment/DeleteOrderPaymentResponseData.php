<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment;

use Shared\Infrastructure\Integrations\ResponseData;

final class DeleteOrderPaymentResponseData extends ResponseData
{
    public function __construct(
        public ?bool $success,
        public ?string $status,
        public ?string $comment,
    ) {}
}

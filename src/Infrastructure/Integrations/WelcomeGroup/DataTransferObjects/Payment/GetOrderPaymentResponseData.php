<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment;

use Domain\Orders\ValueObjects\Payment;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class GetOrderPaymentResponseData extends ResponseData
{
    public function __construct(
        public readonly int $order,
        public readonly ?string $statusComment,
        public readonly string $status,
        public readonly string $type,
        public readonly float $sum,
        public readonly ?string $comment,
        public readonly ExternalData $externalData,
        public readonly int $id,
        public readonly string $created,
        public readonly string $updated
    ) {}

    public function toDomainEntity(): Payment
    {
        return new Payment(
            $this->type,
            (int) ($this->sum * 100),
        );
    }
}

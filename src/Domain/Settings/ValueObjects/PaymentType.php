<?php

declare(strict_types=1);

namespace Domain\Settings\ValueObjects;

use Shared\Domain\ValueObject;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class PaymentType extends ValueObject
{
    public function __construct(
        public readonly ?string $iikoPaymentCode,
        public readonly ?string $welcomeGroupPaymentCode,
    ) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\OrderItems;

use Carbon\CarbonImmutable;
use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class OrderItems extends ResponseData
{
    /**
     * @param  int[]  $foodModifiers
     * @param  FoodModifiersArray[]|array  $foodModifiersArray
     */
    public function __construct(
        public readonly ?string $statusComment,
        public readonly string $status,
        public readonly ?string $comment,
        public readonly int $food,
        public readonly array $foodModifiers,
        public readonly int $order,
        public readonly int $id,
        public readonly CarbonImmutable $created,
        public readonly CarbonImmutable $updated,
        public readonly float $price,
        public readonly float $discount,
        public readonly float $sum,
        public readonly bool $isInternetPayment,
        public readonly FoodObject $foodObject,
        public readonly ?array $foodModifiersArray
    ) {}
}

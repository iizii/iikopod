<?php

declare(strict_types=1);

namespace Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class Items extends Data
{
    /**
     * @param  DataCollection<array-key, Modifiers>  $modifiers
     */
    public function __construct(
        public readonly string $type,
        public readonly Product $product,
        #[DataCollectionOf(Modifiers::class)]
        public readonly DataCollection $modifiers,
        public readonly int $price,
        public readonly int $cost,
        public readonly bool $pricePredefined,
        public readonly string $positionId,
        public readonly int $resultSum,
        public readonly string $status,
        public readonly int $amount,
        public readonly ?string $comment,
        public readonly ?string $whenPrinted,
        public readonly ?Size $size,
        public readonly ?ComboInformation $comboInformation,
    ) {}
}

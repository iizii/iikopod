<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest;

use Presentation\Api\DataTransferObjects\DeliveryOrderUpdateData\ComboInformation;
use Spatie\LaravelData\Data;

final class Items extends Data
{
    /**
     * @param string $productId
     * @param Modifier[] $modifiers
     * @param float $price
     * @param string $type
     * @param int $amount
     * @param string|null $productSizeId
     * @param ComboInformation|null $comboInformation
     * @param string $comment
     */   public function __construct(
        public readonly string $productId,
        public readonly array $modifiers,
        public readonly float $price,
        public readonly string $type,
        public readonly int $amount,
        public readonly ?string $productSizeId,
        public readonly ?ComboInformation $comboInformation,
        public readonly string $comment
    ) {}
}

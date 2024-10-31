<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse;

use Shared\Infrastructure\Integrations\ResponseData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class GetPaymentTypesResponseData extends ResponseData
{
    /**
     * @param  DataCollection<array-key, TerminalGroupData>  $terminalGroups
     */
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $comment,
        public readonly bool $combinable,
        public readonly int $externalRevision,
        public readonly bool $isDeleted,
        public readonly bool $printCheque,
        public readonly string $paymentProcessingType,
        public readonly string $paymentTypeKind,
        #[DataCollectionOf(TerminalGroupData::class)]
        public readonly DataCollection $terminalGroups,
    ) {}
}

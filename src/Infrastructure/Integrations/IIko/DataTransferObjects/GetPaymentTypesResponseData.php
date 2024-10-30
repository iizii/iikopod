<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetPaymentTypesResponseData extends ResponseData
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $comment,
        public readonly bool $combinable,
        public readonly int $externalRevision,
        public readonly array $applicableMarketingCampaigns,
        public readonly bool $isDeleted,
        public readonly bool $printCheque,
        public readonly string $paymentProcessingType,
        public readonly string $paymentTypeKind,
        /** @var TerminalGroupData[] $terminalGroups */
        public readonly array $terminalGroups
    ) {}
}

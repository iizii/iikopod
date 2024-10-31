<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationsResponse;

use Shared\Infrastructure\Integrations\ResponseData;

final class GetOrganizationResponseData extends ResponseData
{
    /**
     * @param  array<array-key, string>|null  $deliveryCityIds
     * @param  array<array-key, string>  $addressLookup
     */
    public function __construct(
        public readonly string $responseType,
        public readonly string $country,
        public readonly string $restaurantAddress,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly bool $useUaeAddressingSystem,
        public readonly string $version,
        public readonly string $currencyIsoName,
        public readonly float $currencyMinimumDenomination,
        public readonly string $countryPhoneCode,
        public readonly bool $marketingSourceRequiredInDelivery,
        public readonly string $defaultDeliveryCityId,
        public readonly ?array $deliveryCityIds,
        public readonly string $deliveryServiceType,
        public readonly ?string $deliveryOrderPaymentSettings,
        public readonly string $defaultCallCenterPaymentTypeId,
        public readonly bool $orderItemCommentEnabled,
        public readonly string $inn,
        public readonly string $addressFormatType,
        public readonly bool $isConfirmationEnabled,
        public readonly int $confirmAllowedIntervalInMinutes,
        public readonly bool $isCloud,
        public readonly bool $isAnonymousGuestsAllowed,
        public readonly array $addressLookup,
        public readonly string $id,
        public readonly string $name,
        public readonly string $code,
    ) {}
}

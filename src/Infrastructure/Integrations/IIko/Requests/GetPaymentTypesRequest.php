<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetPaymentTypesRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetPaymentTypesRequestData $getPaymentTypesRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/payment_types';
    }

    public function data(): GetPaymentTypesRequestData
    {
        return $this->getPaymentTypesRequestData;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }

    /**
     * @return LazyCollection<array-key, GetPaymentTypesResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetPaymentTypesResponseData::collect($response->json('paymentTypes'), LazyCollection::class);
    }
}

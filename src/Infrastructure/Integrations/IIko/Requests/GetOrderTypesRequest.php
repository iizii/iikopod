<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrderTypes\GetOrderTypesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrderTypes\GetOrderTypesResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetOrderTypesRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetOrderTypesRequestData $getOrderTypesRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/deliveries/order_types';
    }

    public function data(): GetOrderTypesRequestData
    {
        return $this->getOrderTypesRequestData;
    }

    /**
     * @return LazyCollection<array-key, GetOrderTypesResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetOrderTypesResponseData::collect(
            $response->json('orderTypes.0.items'),
            LazyCollection::class
        );
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }
}

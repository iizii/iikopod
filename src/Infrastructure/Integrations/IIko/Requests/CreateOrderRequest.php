<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\ResponseData\CreateOrderResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CreateOrderRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private CreateOrderRequestData $createOrderRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/deliveries/create';
    }

    public function data(): CreateOrderRequestData
    {
        return $this->createOrderRequestData;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }

    public function createDtoFromResponse(Response $response): CreateOrderResponseData
    {
        return CreateOrderResponseData::from($response->json());
    }
}

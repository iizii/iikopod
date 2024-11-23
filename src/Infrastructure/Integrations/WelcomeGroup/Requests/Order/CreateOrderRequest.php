<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CreateOrderRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private CreateOrderRequestData $createOrderData) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/order';
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return $this->createOrderData;
    }

    public function createDtoFromResponse(Response $response): CreateOrderResponseData
    {
        return CreateOrderResponseData::from($response->json());
    }
}

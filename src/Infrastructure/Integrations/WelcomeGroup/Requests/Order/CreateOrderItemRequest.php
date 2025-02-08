<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CreateOrderItemRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private CreateOrderItemRequestData $requestData) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/order_item';
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): CreateOrderItemRequestData
    {
        return $this->requestData;
    }

    public function createDtoFromResponse(Response $response): CreateOrderItemResponseData
    {
        return CreateOrderItemResponseData::from($response->json());
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetOrderItemsRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private GetOrderItemsRequestData $requestData) {}

    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/api/order_item';
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return $this->requestData;
    }

    /**
     * @return LazyCollection<array-key, GetOrderItemsResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetOrderItemsResponseData::collect($response->json('items'), LazyCollection::class);
    }
}

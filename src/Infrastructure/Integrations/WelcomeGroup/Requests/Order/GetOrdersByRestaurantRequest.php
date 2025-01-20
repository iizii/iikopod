<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetOrdersByRestaurantRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private GetOrdersByRestaurantRequestData $getOrdersByRestaurantRequestData) {}

    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/api/order';
    }

    //    /**
    //     * @return array<string, string>
    //     */
    //    public function data(): array
    //    {
    //        return [];
    //    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->getOrdersByRestaurantRequestData->toArray();
    }

    /**
     * @return LazyCollection<array-key, GetOrdersByRestaurantResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetOrdersByRestaurantResponseData::collect($response->json('items'), LazyCollection::class);
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

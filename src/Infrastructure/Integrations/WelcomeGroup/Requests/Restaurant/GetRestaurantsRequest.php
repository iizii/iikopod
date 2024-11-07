<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Restaurant;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantResponse\GetRestaurantResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetRestaurantsRequest implements RequestInterface, ResponseDataInterface
{
    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/api/restaurant';
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    /**
     * @return LazyCollection<array-key, GetRestaurantResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetRestaurantResponseData::collect($response->json('items'), LazyCollection::class);
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

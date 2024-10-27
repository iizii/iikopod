<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantsResponse\GetRestaurantsResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final class GetRestaurantsRequest implements RequestInterface, ResponseDataInterface
{
    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/84472714-b690-47df-9de3-f8308c957d54';
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): Collection
    {
        return new Collection(GetRestaurantsResponseData::collect($response->json('items')));
    }
}

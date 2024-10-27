<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantsResponse\GetRestaurantsResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseData;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final class GetRestaurantsRequest implements RequestInterface, ResponseDataInterface
{
    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/restaurant';
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): ResponseData
    {
        return GetRestaurantsResponseData::from($response);
    }
}

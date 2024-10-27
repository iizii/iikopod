<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseData;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final class AuthorizationRequest implements RequestInterface, ResponseDataInterface
{
    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/access_token';
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): ResponseData
    {
        return AuthorizationResponseData::from((array) $response->json());
    }
}

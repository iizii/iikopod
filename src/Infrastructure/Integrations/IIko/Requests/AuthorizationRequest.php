<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Shared\Integrations\RequestInterface;
use Shared\Integrations\RequestMethod;
use Shared\Integrations\ResponseData;
use Shared\Integrations\ResponseDataInterface;

final class AuthorizationRequest implements RequestInterface, ResponseDataInterface
{
    public function getMethod(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function getEndpoint(): string
    {
        return '/todos/1';
    }

    public function createDtoFromResponse(Response $response): ResponseData
    {
        return AuthorizationResponseData::from((array) $response->json());
    }
}

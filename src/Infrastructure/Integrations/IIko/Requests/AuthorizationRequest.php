<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class AuthorizationRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(public string $token) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }

    public function endpoint(): string
    {
        return '/api/1/access_token';
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return [
            'apiLogin' => $this->token,
        ];
    }

    public function createDtoFromResponse(Response $response): AuthorizationResponseData
    {
        return AuthorizationResponseData::from($response->json());
    }
}

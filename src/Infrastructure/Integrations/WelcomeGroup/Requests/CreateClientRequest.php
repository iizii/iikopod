<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\CreateClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\CreateClientResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CreateClientRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private CreateClientRequestData $data) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/client';
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->data->toArray();
    }

    public function createDtoFromResponse(Response $response): CreateClientResponseData
    {
        return CreateClientResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

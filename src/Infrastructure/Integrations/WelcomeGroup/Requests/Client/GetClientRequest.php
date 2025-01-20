<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Client;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetClientRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private GetClientRequestData $data) {}

    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/api/client/'.$this->data->id;
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): GetClientResponseData
    {
        return GetClientResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

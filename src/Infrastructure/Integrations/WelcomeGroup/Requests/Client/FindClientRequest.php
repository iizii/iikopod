<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Client;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\FindClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\FindClientResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class FindClientRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private FindClientRequestData $data) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/client/search';
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->data->toArray();
    }

    /**
     * @return LazyCollection<array-key, FindClientResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return FindClientResponseData::collect($response->json('items'), LazyCollection::class);
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

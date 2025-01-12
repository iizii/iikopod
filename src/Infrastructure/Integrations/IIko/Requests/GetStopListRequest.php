<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetStopListRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetStopListRequestData $getStopListRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/stop_lists';
    }

    public function data(): GetStopListRequestData
    {
        return $this->getStopListRequestData;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }

    /**
     * @return LazyCollection<array-key, GetStopListResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetStopListResponseData::collect(
            $response->json('terminalGroupStopLists.0.items.0.items'),
            LazyCollection::class
        );
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse\GetAvailableTerminalsResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetAvailableTerminalsRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetAvailableTerminalsRequestData $getAvailableTerminalsRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/terminal_groups';
    }

    public function data(): GetAvailableTerminalsRequestData
    {
        return $this->getAvailableTerminalsRequestData;
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
        return GetAvailableTerminalsResponseData::collect(
            $response->json('terminalGroups'),
            LazyCollection::class
        );
    }
}

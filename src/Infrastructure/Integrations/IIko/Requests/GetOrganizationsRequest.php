<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetOrganizationsResponse\GetOrganizationResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetOrganizationsRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetOrganizationRequestData $getOrganizationRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/organizations';
    }

    public function data(): GetOrganizationRequestData
    {
        return $this->getOrganizationRequestData;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }

    /**
     * @return LazyCollection<array-key, GetOrganizationResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetOrganizationResponseData::collect($response->json('organizations'), LazyCollection::class);
    }
}

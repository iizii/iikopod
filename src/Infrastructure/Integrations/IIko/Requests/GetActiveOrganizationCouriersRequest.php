<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetActiveOrganizationCouriersRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetActiveOrganizationCouriersRequestData $getActiveOrganizationCouriersRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/employees/couriers/active_location';
    }

    public function data(): GetActiveOrganizationCouriersRequestData
    {
        return $this->getActiveOrganizationCouriersRequestData;
    }

    /**
     * @return LazyCollection<array-key, GetActiveOrganizationCouriersResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetActiveOrganizationCouriersResponseData::collect(
            $response->json('activeCourierLocations.0.items'),
            LazyCollection::class
        );
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }
}

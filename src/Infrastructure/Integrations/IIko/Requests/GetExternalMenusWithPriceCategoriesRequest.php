<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetExternalMenusWithPriceCategoriesResponse\GetExternalMenusWithPriceCategoriesResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetExternalMenusWithPriceCategoriesRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private GetExternalMenusWithPriceCategoriesRequestData $getExternalMenusWithPriceCategoriesRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/2/menu';
    }

    public function data(): GetExternalMenusWithPriceCategoriesRequestData
    {
        return $this->getExternalMenusWithPriceCategoriesRequestData;
    }

    public function createDtoFromResponse(Response $response): GetExternalMenusWithPriceCategoriesResponseData
    {
        return GetExternalMenusWithPriceCategoriesResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }
}

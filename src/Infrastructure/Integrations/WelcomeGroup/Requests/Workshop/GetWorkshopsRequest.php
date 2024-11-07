<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Workshop;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetWorkshopResponse\GetWorkshopResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetWorkshopsRequest implements RequestInterface, ResponseDataInterface
{
    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return '/api/workshop';
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return [];
    }

    /**
     * @return LazyCollection<array-key, GetWorkshopResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return GetWorkshopResponseData::collect($response->json('items'), LazyCollection::class);
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

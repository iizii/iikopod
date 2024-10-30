<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
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

    public function data(): array|Arrayable
    {
        return [];
    }

    /**
     * @return Collection<array-key, GetWorkshopResponseData>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        /**  @phpstan-ignore argument.type */
        return new Collection(GetWorkshopResponseData::collect($response->json('items')));
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Phone;

use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class FindPhoneRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private FindPhoneRequestData $data) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/phone/search';
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return $this->data->toArray();
    }

    /**
     * @return LazyCollection<array-key, FindPhoneResponseData>
     */
    public function createDtoFromResponse(Response $response): LazyCollection
    {
        return FindPhoneResponseData::collect($response->json('items'), LazyCollection::class);
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Address;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\GetAddressResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetAddressRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private int $id) {}

    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return "/address/{$this->id}";
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    /**
     * @param Response $response
     * @return GetAddressResponseData
     */
    public function createDtoFromResponse(Response $response): GetAddressResponseData
    {
        return GetAddressResponseData::from($response->json());
    }
}

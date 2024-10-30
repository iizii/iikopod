<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantResponse\GetRestaurantResponseData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetRestaurantRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(public IntegerId $integerId) {}

    public function method(): RequestMethod
    {
        return RequestMethod::GET;
    }

    public function endpoint(): string
    {
        return sprintf('/api/restaurant/%s', $this->integerId->id);
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): GetRestaurantResponseData
    {
        return GetRestaurantResponseData::from($response->json());
    }

    public function headers(): array
    {
        return [];
    }
}

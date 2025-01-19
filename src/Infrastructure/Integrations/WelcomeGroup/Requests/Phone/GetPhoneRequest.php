<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Phone;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class GetPhoneRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private GetPhoneRequestData $data) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/phone/'.$this->data->id;
    }

    /**
     * @return array<string, string>
     */
    public function data(): array
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): GetPhoneResponseData
    {
        return GetPhoneResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [];
    }
}

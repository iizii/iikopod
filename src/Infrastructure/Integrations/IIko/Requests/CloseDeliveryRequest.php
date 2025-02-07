<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\CancelOrCloseRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest\UpdateOrderRequestData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CloseDeliveryRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private CancelOrCloseRequestData $cancelOrCloseRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/deliveries/close';
    }

    public function data(): CancelOrCloseRequestData
    {
        return $this->cancelOrCloseRequestData;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }

    public function createDtoFromResponse(Response $response): iterable|\Shared\Infrastructure\Integrations\ResponseData
    {
        return $response->json();
    }
}

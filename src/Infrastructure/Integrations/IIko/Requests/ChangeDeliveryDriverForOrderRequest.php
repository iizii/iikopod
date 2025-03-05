<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class ChangeDeliveryDriverForOrderRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private ChangeDeliveryDriverForOrderRequestData $changeDeliveryDriverForOrderRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/deliveries/change_driver_info';
    }

    public function data(): ChangeDeliveryDriverForOrderRequestData
    {
        return $this->changeDeliveryDriverForOrderRequestData;
    }

    public function createDtoFromResponse(Response $response): ChangeDeliveryDriverForOrderResponseData
    {
        return ChangeDeliveryDriverForOrderResponseData::from($response->json());
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return ['Authorization' => sprintf('Bearer %s', $this->authToken)];
    }
}

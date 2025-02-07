<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Requests;

use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest\UpdateOrderRequestData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class UpdateDeliveryStatus implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private UpdateOrderRequestData $updateOrderRequestData,
        private string $authToken,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/1/deliveries/update_order_delivery_status';
    }

    public function data(): UpdateOrderRequestData
    {
        return $this->updateOrderRequestData;
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

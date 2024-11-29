<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentResponseData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CreateOrderPaymentRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private CreateOrderPaymentRequestData $requestData) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/payment';
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return $this->requestData;
    }

    public function createDtoFromResponse(Response $response): CreateOrderPaymentResponseData
    {
        return CreateOrderPaymentResponseData::from($response->json());
    }
}

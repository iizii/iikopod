<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderResponseData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class ApproveOrderRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private IntegerId $orderId) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return "/api/order/approve/start/{$this->orderId->id}";
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): iterable|\Shared\Infrastructure\Integrations\ResponseData
    {
        return $response->json();
    }
}

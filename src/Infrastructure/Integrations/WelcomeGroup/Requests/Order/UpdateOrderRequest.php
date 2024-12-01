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

final readonly class UpdateOrderRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private IntegerId $orderId, private UpdateOrderRequestData $updateOrderRequestData) {}

    public function method(): RequestMethod
    {
        return RequestMethod::PATCH;
    }

    public function endpoint(): string
    {
        return "/api/order/{$this->orderId->id}";
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return $this->updateOrderRequestData;
    }

    public function createDtoFromResponse(Response $response): UpdateOrderResponseData
    {
        return UpdateOrderResponseData::from($response->json());
    }
}

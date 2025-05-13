<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderItemRequestData;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class UpdateOrderItemRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(
        private string $externalId,
        private UpdateOrderItemRequestData $data,
    ) {}

    public function method(): RequestMethod
    {
        return RequestMethod::PATCH;
    }

    public function endpoint(): string
    {
        return "/api/order_item/{$this->externalId}";
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): UpdateOrderItemRequestData
    {
        return $this->data;
    }

    public function createDtoFromResponse(Response $response): iterable|\Shared\Infrastructure\Integrations\ResponseData
    {
        return $response->json();
    }
}

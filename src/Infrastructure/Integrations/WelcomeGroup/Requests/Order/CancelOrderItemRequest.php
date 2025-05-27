<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class CancelOrderItemRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private IntegerId $orderItemId) {}

    public function method(): RequestMethod
    {
        return RequestMethod::POST;
    }

    public function endpoint(): string
    {
        return '/api/order_item/operator/reject/'.$this->orderItemId->id;
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array
    {
        return ['comment' => 'Отмена позиции модулем связи'];
    }

    public function createDtoFromResponse(Response $response): array
    {
        return $response->json();
    }
}

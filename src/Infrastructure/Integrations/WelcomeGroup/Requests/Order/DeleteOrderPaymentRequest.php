<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Requests\Order;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\DeleteOrderPaymentResponseData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\RequestMethod;
use Shared\Infrastructure\Integrations\ResponseDataInterface;

final readonly class DeleteOrderPaymentRequest implements RequestInterface, ResponseDataInterface
{
    public function __construct(private IntegerId $welcomeGroupExternalId) {}

    public function method(): RequestMethod
    {
        return RequestMethod::DELETE;
    }

    public function endpoint(): string
    {
        return "/api/payment/{$this->welcomeGroupExternalId->id}";
    }

    public function headers(): array|Arrayable
    {
        return [];
    }

    public function data(): array|Arrayable
    {
        return [];
    }

    public function createDtoFromResponse(Response $response): DeleteOrderPaymentResponseData
    {
        return DeleteOrderPaymentResponseData::from($response->json());
    }
}

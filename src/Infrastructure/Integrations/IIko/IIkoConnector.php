<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\ResponseData\CreateOrderResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse\GetAvailableTerminalsResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse\GetMenuResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListResponseData;
use Infrastructure\Integrations\IIko\Events\IIkoRequestFailedEvent;
use Infrastructure\Integrations\IIko\Events\IIkoRequestSuccessesEvent;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Infrastructure\Integrations\IIko\Requests\CreateOrderRequest;
use Infrastructure\Integrations\IIko\Requests\GetAvailableTerminalsRequest;
use Infrastructure\Integrations\IIko\Requests\GetMenuRequest;
use Infrastructure\Integrations\IIko\Requests\GetStopListRequest;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\AbstractConnector;
use Shared\Infrastructure\Integrations\RequestInterface;

final readonly class IIkoConnector extends AbstractConnector implements IikoConnectorInterface
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getMenu(
        GetMenuRequestData $getMenuRequestData,
        string $authToken,
    ): GetMenuResponseData {
        /** @var GetMenuResponseData $response */
        $response = $this->send(
            new GetMenuRequest(
                $getMenuRequestData,
                $authToken,
            ),
        );

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createOrder(
        CreateOrderRequestData $createOrderRequestData,
        string $authToken,
    ): CreateOrderResponseData {
        /** @var CreateOrderResponseData */
        return $this->send(
            new CreateOrderRequest(
                $createOrderRequestData,
                $authToken,
            ),
        );
    }

    /**
     * @return LazyCollection<array-key, GetStopListResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getStopLists(string $organizationId, string $authToken): LazyCollection
    {
        /** @var LazyCollection<array-key, GetStopListResponseData> $response */
        $response = $this->send(
            new GetStopListRequest(
                new GetStopListRequestData([$organizationId]),
                $authToken,
            )
        );

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getAvailableTerminals(stringId $organizationId, string $authToken): LazyCollection
    {
        /** @var LazyCollection<array-key, GetAvailableTerminalsResponseData> */
        return $this
            ->send(
                new GetAvailableTerminalsRequest(
                    new GetAvailableTerminalsRequestData(
                        [$organizationId->id]
                    ),
                    $authToken
                )
            );
    }

    protected function getRequestException(Response $response, \Throwable $clientException): \Throwable
    {
        return new IIkoIntegrationException(
            $clientException->getMessage(),
            $clientException->getCode(),
            $clientException,
        );
    }

    /**
     * @return iterable<class-string>
     */
    protected function getRequestSuccessEvents(): iterable
    {
        yield IIkoRequestSuccessesEvent::class;
    }

    /**
     * @return iterable<class-string>
     */
    protected function getRequestErrorEvents(): iterable
    {
        yield IIkoRequestFailedEvent::class;
    }

    protected function headers(RequestInterface $request): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }
}

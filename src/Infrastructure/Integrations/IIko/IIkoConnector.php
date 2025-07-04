<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\AddOrderItemsRequest\AddOrderItemsRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CancelOrCloseRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangePaymentsForOrder\ChangePaymentsForOrder;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\ResponseData\CreateOrderResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetAvailableTerminalsResponse\GetAvailableTerminalsResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse\GetMenuResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetPaymentTypesResponse\GetPaymentTypesResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetStopListResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest\UpdateOrderRequestData;
use Infrastructure\Integrations\IIko\Events\IIkoRequestFailedEvent;
use Infrastructure\Integrations\IIko\Events\IIkoRequestSuccessesEvent;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Infrastructure\Integrations\IIko\Requests\AddOrderItemsRequest;
use Infrastructure\Integrations\IIko\Requests\CancelDeliveryRequest;
use Infrastructure\Integrations\IIko\Requests\ChangeDeliveryDriverForOrderRequest;
use Infrastructure\Integrations\IIko\Requests\ChangeOrderPaymentsRequest;
use Infrastructure\Integrations\IIko\Requests\CloseDeliveryRequest;
use Infrastructure\Integrations\IIko\Requests\CreateOrderRequest;
use Infrastructure\Integrations\IIko\Requests\GetActiveOrganizationCouriersRequest;
use Infrastructure\Integrations\IIko\Requests\GetAvailableTerminalsRequest;
use Infrastructure\Integrations\IIko\Requests\GetMenuRequest;
use Infrastructure\Integrations\IIko\Requests\GetPaymentTypesRequest;
use Infrastructure\Integrations\IIko\Requests\GetStopListRequest;
use Infrastructure\Integrations\IIko\Requests\UpdateDeliveryStatus;
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
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateDeliveryStatus(
        UpdateOrderRequestData $updateOrderRequestData,
        string $authToken,
    ) {
        /** @var CreateOrderResponseData */
        return $this->send(
            new UpdateDeliveryStatus(
                $updateOrderRequestData,
                $authToken,
            ),
        );
    }

    public function closeOrder(CancelOrCloseRequestData $cancelOrCloseRequestData, string $authToken)
    {
        return $this
            ->send(new CloseDeliveryRequest(
                $cancelOrCloseRequestData,
                $authToken
            ));
    }

    public function rejectOrder(CancelOrCloseRequestData $cancelOrCloseRequestData, string $authToken)
    {
        return $this
            ->send(new CancelDeliveryRequest(
                $cancelOrCloseRequestData,
                $authToken
            ));
    }

    /**
     * @return LazyCollection<array-key, GetActiveOrganizationCouriersResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function getActiveOrganizationCouriers(GetActiveOrganizationCouriersRequestData $getActiveOrganizationCouriersRequestData, string $authToken): LazyCollection
    {
        /** @var LazyCollection<array-key, GetActiveOrganizationCouriersResponseData> */
        return $this
            ->send(new GetActiveOrganizationCouriersRequest(
                $getActiveOrganizationCouriersRequestData,
                $authToken
            ));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function changeDeliveryDriverForOrder(ChangeDeliveryDriverForOrderRequestData $changeDeliveryDriverForOrderRequestData, string $authToken): ChangeDeliveryDriverForOrderResponseData
    {
        /** @var ChangeDeliveryDriverForOrderResponseData */
        return $this
            ->send(new ChangeDeliveryDriverForOrderRequest(
                $changeDeliveryDriverForOrderRequestData,
                $authToken
            ));
    }

    /**
     * Добавляет новые позиции к существующему заказу в IIKO
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function addOrderItems(AddOrderItemsRequestData $data, string $token): void
    {
        $this->send(new AddOrderItemsRequest($data, $token));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function changePaymentsForOrder(ChangePaymentsForOrder $data, string $token): void
    {
        $this->send(new ChangeOrderPaymentsRequest($data, $token));
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

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getPaymentTypes(stringId $organizationId, string $authToken): LazyCollection
    {
        /** @var LazyCollection<array-key, GetPaymentTypesResponseData> */
        return $this
            ->send(
                new GetPaymentTypesRequest(
                    new GetPaymentTypesRequestData(
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

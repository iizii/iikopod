<?php

declare(strict_types=1);

namespace Domain\Integrations\Iiko;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\IIko\DataTransferObjects\CancelOrCloseRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\ChangeDeliveryDriverForOrderResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\CreateOrderRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\CreateOrderRequest\ResponseData\CreateOrderResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetActiveOrganizationCouriersRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse\GetMenuResponseData;
use Infrastructure\Integrations\IIko\DataTransferObjects\UpdateOrderRequest\UpdateOrderRequestData;
use Shared\Domain\ValueObjects\StringId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\ResponseData;

interface IikoConnectorInterface
{
    /**
     * @return Response|ResponseData|iterable<array-key, ResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function send(RequestInterface $request): Response|ResponseData|iterable;

    /**
     * @return iterable<Response|ResponseData>
     */
    public function sendAsync(RequestInterface ...$requests): iterable;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getMenu(
        GetMenuRequestData $getMenuRequestData,
        string $authToken,
    ): GetMenuResponseData;

    public function getStopLists(string $organizationId, string $authToken): LazyCollection;

    public function getAvailableTerminals(stringId $organizationId, string $authToken): LazyCollection;

    public function getPaymentTypes(stringId $organizationId, string $authToken): LazyCollection;

    public function createOrder(
        CreateOrderRequestData $createOrderRequestData,
        string $authToken,
    ): CreateOrderResponseData;

    public function closeOrder(CancelOrCloseRequestData $cancelOrCloseRequestData, string $authToken);

    public function rejectOrder(CancelOrCloseRequestData $cancelOrCloseRequestData, string $authToken);

    public function updateDeliveryStatus(UpdateOrderRequestData $updateOrderRequestData, string $authToken);

    public function getActiveOrganizationCouriers(GetActiveOrganizationCouriersRequestData $getActiveOrganizationCouriersRequestData, string $authToken): LazyCollection;

    public function changeDeliveryDriverForOrder(ChangeDeliveryDriverForOrderRequestData $changeDeliveryDriverForOrderRequestData, string $authToken): ChangeDeliveryDriverForOrderResponseData;
}

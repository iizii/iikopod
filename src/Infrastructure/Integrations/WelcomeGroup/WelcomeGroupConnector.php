<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup;

use Carbon\CarbonImmutable;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\CreateAddressRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\CreateAddressResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\GetAddressResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\CreateClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\CreateClientResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\FindClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\FindClientResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\GetClientResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\CreateFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\CreateFoodResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Food\EditFoodResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory\CreateFoodCategoryResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\CreateFoodModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\EditFoodModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodModifier\EditFoodModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\GetRestaurantResponse\GetRestaurantResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\EditModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\EditModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\GetOrdersByRestaurantResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\GetOrderItemsResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\GetOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\GetPhoneResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestFailedEvent;
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestSuccessesEvent;
use Infrastructure\Integrations\WelcomeGroup\Exceptions\WelcomeGroupIntegrationException;
use Infrastructure\Integrations\WelcomeGroup\Requests\Address\CreateAddressRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Address\GetAddressRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Client\CreateClientRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Client\FindClientRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Client\GetClientRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\CreateFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\EditFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\CreateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\UpdateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\CreateFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\EditFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\CreateModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\EditModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\CreateModifierTypeRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\EditModifierTypeRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\ApproveOrderRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\CreateOrderItemRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\CreateOrderPaymentRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\CreateOrderRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\GetOrderItemsRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\GetOrderPaymentRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\GetOrdersByRestaurantRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Order\UpdateOrderRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Phone\CreatePhoneRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Phone\FindPhoneRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Phone\GetPhoneRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Restaurant\GetRestaurantRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantFood\CreateRestaurantFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantFood\EditRestaurantFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantModifier\CreateRestaurantModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\RestaurantModifier\EditRestaurantModifierRequest;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\AbstractConnector;
use Shared\Infrastructure\Integrations\ConnectorLogger;
use Shared\Infrastructure\Integrations\RequestInterface;

final readonly class WelcomeGroupConnector extends AbstractConnector implements WelcomeGroupConnectorInterface
{
    public function __construct(
        PendingRequest $pendingRequest,
        Dispatcher $eventDispatcher,
        ConnectorLogger $logger,
        private SignatureCompiler $signatureCompiler,
    ) {
        parent::__construct($pendingRequest, $eventDispatcher, $logger);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData,
    ): CreateFoodCategoryResponseData {
        /** @var CreateFoodCategoryResponseData $response */
        $response = $this->send(new CreateFoodCategoryRequest($createFoodCategoryRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getRestaurant(IntegerId $id): GetRestaurantResponseData
    {
        /** @var GetRestaurantResponseData $response */
        $response = $this->send(new GetRestaurantRequest($id));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateFoodCategory(
        CreateFoodCategoryRequestData $createFoodCategoryRequestData,
        IntegerId $id,
    ): CreateFoodCategoryResponseData {
        /** @var CreateFoodCategoryResponseData $response */
        $response = $this->send(new UpdateFoodCategoryRequest($createFoodCategoryRequestData, $id));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFood(CreateFoodRequestData $createFoodRequestData): CreateFoodResponseData
    {
        /** @var CreateFoodResponseData $response */
        $response = $this->send(new CreateFoodRequest($createFoodRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateModifier(EditModifierRequestData $editModifierRequestData, IntegerId $id): EditModifierResponseData
    {
        /** @var EditModifierResponseData $response */
        $response = $this->send(new EditModifierRequest($id->id, $editModifierRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateFood(EditFoodRequestData $editFoodRequestData, IntegerId $Id): EditFoodResponseData
    {
        /** @var EditFoodResponseData $response */
        $response = $this->send(new EditFoodRequest($editFoodRequestData, $Id));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createModifierType(CreateModifierTypeRequestData $createModifierTypeRequestData,
    ): CreateModifierTypeResponseData {
        /** @var CreateModifierTypeResponseData $response */
        $response = $this->send(new CreateModifierTypeRequest($createModifierTypeRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateModifierType(EditModifierTypeRequestData $editModifierTypeRequestData, IntegerId $id): EditModifierTypeResponseData
    {
        /** @var EditModifierTypeResponseData $response */
        $response = $this->send(new EditModifierTypeRequest($id->id, $editModifierTypeRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createModifier(CreateModifierRequestData $createModifierRequestData): CreateModifierResponseData
    {
        /** @var CreateModifierResponseData $response */
        $response = $this->send(new CreateModifierRequest($createModifierRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFoodModifier(CreateFoodModifierRequestData $createFoodModifierRequestData,
    ): CreateFoodModifierResponseData {
        /** @var CreateFoodModifierResponseData $response */
        $response = $this->send(new CreateFoodModifierRequest($createFoodModifierRequestData));

        return $response;
    }

    /**
     * @return LazyCollection<array-key, FindClientResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function findClient(FindClientRequestData $findClientRequestData): LazyCollection
    {
        /** @var LazyCollection<array-key, FindClientResponseData> $response */
        $response = $this->send(new FindClientRequest($findClientRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createClient(CreateClientRequestData $createClientRequestData): CreateClientResponseData
    {
        /** @var CreateClientResponseData $response */
        $response = $this->send(new CreateClientRequest($createClientRequestData));

        return $response;
    }

    /**
     * @return LazyCollection<array-key, FindPhoneResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function findPhone(FindPhoneRequestData $findPhoneRequestData): LazyCollection
    {
        /** @var LazyCollection<array-key, FindPhoneResponseData> $response */
        $response = $this->send(new FindPhoneRequest($findPhoneRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createPhone(CreatePhoneRequestData $createPhoneRequestData): CreatePhoneResponseData
    {
        /** @var CreatePhoneResponseData $response */
        $response = $this->send(new CreatePhoneRequest($createPhoneRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createAddress(CreateAddressRequestData $createAddressRequestData): CreateAddressResponseData
    {
        /** @var CreateAddressResponseData $response */
        $response = $this->send(new CreateAddressRequest($createAddressRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createOrder(CreateOrderRequestData $createOrderData): CreateOrderResponseData
    {
        /** @var CreateOrderResponseData $response */
        $response = $this->send(new CreateOrderRequest($createOrderData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateOrder(IntegerId $orderId, UpdateOrderRequestData $updateOrderRequestData): UpdateOrderResponseData
    {
        /** @var UpdateOrderResponseData $response */
        $response = $this->send(new UpdateOrderRequest($orderId, $updateOrderRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function approveOrder(IntegerId $orderId): UpdateOrderResponseData
    {
        /** @var UpdateOrderResponseData $response */
        $response = $this->send(new ApproveOrderRequest($orderId));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createOrderItem(CreateOrderItemRequestData $createOrderItemRequestData): CreateOrderItemResponseData
    {
        /** @var CreateOrderItemResponseData $response */
        $response = $this->send(new CreateOrderItemRequest($createOrderItemRequestData));

        return $response;
    }

    /**
     * @return LazyCollection<array-key, GetOrderItemsResponseData>
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getOrderItems(IntegerId $id): LazyCollection
    {
        /** @var LazyCollection<array-key, GetOrderItemsResponseData> */
        return $this
            ->send(new GetOrderItemsRequest(
                new GetOrderItemsRequestData($id->id)
            ));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createPayment(CreateOrderPaymentRequestData $createOrderPaymentRequestData): CreateOrderPaymentResponseData
    {
        /** @var CreateOrderPaymentResponseData $response */
        $response = $this->send(new CreateOrderPaymentRequest($createOrderPaymentRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getOrderPayment(GetOrderPaymentRequestData $getOrderPaymentRequestData): LazyCollection
    {
        /** @var LazyCollection<array-key, GetOrderPaymentResponseData> */
        return $this->send(new GetOrderPaymentRequest($getOrderPaymentRequestData));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateFoodModifier(EditFoodModifierRequestData $editFoodModifierRequestData, IntegerId $id): DataTransferObjects\FoodModifier\EditFoodModifierResponseData
    {
        /** @var EditFoodModifierResponseData $response */
        $response = $this->send(new EditFoodModifierRequest($id->id, $editFoodModifierRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createRestaurantFood(CreateRestaurantFoodRequestData $createRestaurantFoodRequestData): CreateRestaurantFoodResponseData
    {
        /** @var CreateRestaurantFoodResponseData $response */
        $response = $this->send(new CreateRestaurantFoodRequest($createRestaurantFoodRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createRestaurantModifier(CreateRestaurantModifierRequestData $createRestaurantModifierRequestData): CreateRestaurantModifierResponseData
    {
        /** @var CreateRestaurantModifierResponseData $response */
        $response = $this->send(new CreateRestaurantModifierRequest($createRestaurantModifierRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateRestaurantModifier(EditRestaurantModifierRequestData $editRestaurantModifierRequestData, IntegerId $id): DataTransferObjects\RestaurantModifier\EditRestaurantModifierResponseData
    {
        /** @var EditRestaurantModifierResponseData $response */
        $response = $this->send(new EditRestaurantModifierRequest($id->id, $editRestaurantModifierRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateRestaurantFood(EditRestaurantFoodRequestData $editRestaurantFoodRequestData, IntegerId $id): DataTransferObjects\RestaurantFood\EditRestaurantFoodResponseData
    {
        /** @var EditRestaurantFoodResponseData */
        return $this->send(new EditRestaurantFoodRequest((int) $id->id, $editRestaurantFoodRequestData));
    }

    /**
     * @return LazyCollection<array-key, GetOrdersByRestaurantResponseData>
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getOrdersByRestaurantId(GetOrdersByRestaurantRequestData $getOrdersByRestaurantRequestData): LazyCollection
    {
        /** @var LazyCollection<array-key, GetOrdersByRestaurantResponseData> */
        return $this->send(new GetOrdersByRestaurantRequest($getOrdersByRestaurantRequestData));
    }

    public function getClient(IntegerId $id): GetClientResponseData
    {
        /** @var GetClientResponseData */
        return $this->send(new GetClientRequest(
            new GetClientRequestData($id->id)
        ));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getPhone(IntegerId $id): GetPhoneResponseData
    {
        /** @var GetPhoneResponseData */
        return $this->send(new GetPhoneRequest(
            new GetPhoneRequestData($id->id)
        ));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getAddress(IntegerId $id): GetAddressResponseData
    {
        /** @var GetAddressResponseData $response */
        $response = $this->send(new GetAddressRequest($id->id));

        return $response;
    }

    protected function getRequestException(Response $response, \Throwable $clientException): \Throwable
    {
        return new WelcomeGroupIntegrationException(
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
        yield WelcomeGroupRequestSuccessesEvent::class;
    }

    /**
     * @return iterable<class-string>
     */
    protected function getRequestErrorEvents(): iterable
    {
        yield WelcomeGroupRequestFailedEvent::class;
    }

    /**
     * @return array{
     *     Content-Type: 'application/json',
     *     X-Api-Date: string,
     *     X-API-User: string,
     *     X-Api-Signature: string
     * }
     */
    protected function headers(RequestInterface $request): array
    {
        $date = new CarbonImmutable();

        return [
            'Content-Type' => 'application/json',
            'X-Api-Date' => $date->toRfc7231String(),
            'X-API-User' => $this->signatureCompiler->user,
            'X-Api-Signature' => $this->signatureCompiler->compile($request, $date),
        ];
    }
}

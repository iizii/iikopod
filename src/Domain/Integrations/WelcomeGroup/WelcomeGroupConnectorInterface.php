<?php

declare(strict_types=1);

namespace Domain\Integrations\WelcomeGroup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\CreateAddressRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Address\CreateAddressResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\CreateClientRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Client\CreateClientResponseData;
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
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\UpdateOrderResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Payment\CreateOrderPaymentResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\FindPhoneResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\CreateRestaurantFoodResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantFood\EditRestaurantFoodResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\CreateRestaurantModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\RestaurantModifier\EditRestaurantModifierResponseData;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\RequestInterface;
use Shared\Infrastructure\Integrations\ResponseData;

interface WelcomeGroupConnectorInterface
{
    /**
     * @return Response|ResponseData|iterable<array-key, ResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function send(RequestInterface $request): Response|ResponseData|iterable;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData): CreateFoodCategoryResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData, IntegerId $id): CreateFoodCategoryResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFood(CreateFoodRequestData $createFoodRequestData): CreateFoodResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateFood(EditFoodRequestData $editFoodRequestData, IntegerId $Id): EditFoodResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createModifierType(CreateModifierTypeRequestData $createModifierTypeRequestData): CreateModifierTypeResponseData;

    public function updateModifierType(EditModifierTypeRequestData $editModifierTypeRequestData, IntegerId $id): EditModifierTypeResponseData;

    public function updateFoodModifier(EditFoodModifierRequestData $editFoodModifierRequestData, IntegerId $id): EditFoodModifierResponseData;

    public function updateRestaurantModifier(EditRestaurantModifierRequestData $editRestaurantModifierRequestData, IntegerId $id): EditRestaurantModifierResponseData;

    public function createRestaurantFood(CreateRestaurantFoodRequestData $createRestaurantFoodRequestData): CreateRestaurantFoodResponseData;

    public function updateRestaurantFood(EditRestaurantFoodRequestData $editRestaurantFoodRequestData, IntegerId $id): EditRestaurantFoodResponseData;

    //    public function deleteModifierType(EditModifierTypeRequestData $editModifierTypeRequestData, IntegerId $id): EditModifierTypeResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createModifier(CreateModifierRequestData $createModifierRequestData): CreateModifierResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateModifier(EditModifierRequestData $editModifierRequestData, IntegerId $id): EditModifierResponseData;

    public function createFoodModifier(CreateFoodModifierRequestData $createFoodModifierRequestData): CreateFoodModifierResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createClient(CreateClientRequestData $createClientRequestData): CreateClientResponseData;

    /**
     * @return LazyCollection<array-key, FindPhoneResponseData>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function findPhone(FindPhoneRequestData $findPhoneRequestData): LazyCollection;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createPhone(CreatePhoneRequestData $createPhoneRequestData): CreatePhoneResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createAddress(CreateAddressRequestData $createAddressRequestData): CreateAddressResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createOrder(CreateOrderRequestData $createOrderData): CreateOrderResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateOrder(IntegerId $orderId, UpdateOrderRequestData $updateOrderRequestData): UpdateOrderResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createOrderItem(CreateOrderItemRequestData $createOrderItemRequestData): CreateOrderItemResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createPayment(CreateOrderPaymentRequestData $createOrderPaymentRequestData): CreateOrderPaymentResponseData;

    public function createRestaurantModifier(CreateRestaurantModifierRequestData $createRestaurantModifierRequestData): CreateRestaurantModifierResponseData;

    public function getOrdersByRestaurantId(GetOrdersByRestaurantRequestData $getOrdersByRestaurantRequestData): LazyCollection;
}

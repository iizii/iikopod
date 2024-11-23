<?php

declare(strict_types=1);

namespace Domain\Integrations\WelcomeGroup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
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
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Order\CreateOrderResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\OrderItem\CreateOrderItemResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Phone\CreatePhoneResponseData;
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

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createModifier(CreateModifierRequestData $createModifierRequestData): CreateModifierResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFoodModifier(CreateFoodModifierRequestData $createFoodModifierRequestData): CreateFoodModifierResponseData;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createClient(CreateClientRequestData $createClientRequestData): CreateClientResponseData;

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
    public function createOrderItem(CreateOrderItemRequestData $createOrderItemRequestData): CreateOrderItemResponseData;
}

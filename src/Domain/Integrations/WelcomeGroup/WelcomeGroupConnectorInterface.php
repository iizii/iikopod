<?php

declare(strict_types=1);

namespace Domain\Integrations\WelcomeGroup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
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
     * @return iterable<Response|ResponseData>
     */
    public function sendAsync(RequestInterface ...$requests): iterable;

    public function createFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData): CreateFoodCategoryResponseData;

    public function updateFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData, IntegerId $id): CreateFoodCategoryResponseData;

    public function createFood(CreateFoodRequestData $createFoodRequestData): CreateFoodResponseData;

    public function updateFood(EditFoodRequestData $editFoodRequestData, IntegerId $Id): EditFoodResponseData;

    public function createModifierType(CreateModifierTypeRequestData $createModifierTypeRequestData): CreateModifierTypeResponseData;

    public function updateModifierType(EditModifierTypeRequestData $editModifierTypeRequestData, IntegerId $id): EditModifierTypeResponseData;

    public function updateFoodModifier(EditFoodModifierRequestData $editFoodModifierRequestData, IntegerId $id): EditFoodModifierResponseData;

    public function updateRestaurantModifier(EditRestaurantModifierRequestData $editRestaurantModifierRequestData, IntegerId $id): EditRestaurantModifierResponseData;

    public function createRestaurantFood(CreateRestaurantFoodRequestData $createRestaurantFoodRequestData): CreateRestaurantFoodResponseData;

    public function updateRestaurantFood(EditRestaurantFoodRequestData $editRestaurantFoodRequest, IntegerId $id): EditRestaurantFoodResponseData;

    //    public function deleteModifierType(EditModifierTypeRequestData $editModifierTypeRequestData, IntegerId $id): EditModifierTypeResponseData;

    public function createModifier(CreateModifierRequestData $createModifierRequestData): CreateModifierResponseData;

    public function updateModifier(EditModifierRequestData $editModifierRequestData, IntegerId $id): EditModifierResponseData;

    public function createFoodModifier(CreateFoodModifierRequestData $createFoodModifierRequestData): CreateFoodModifierResponseData;

    public function createRestaurantModifier(CreateRestaurantModifierRequestData $createRestaurantModifierRequestData): CreateRestaurantModifierResponseData;
}

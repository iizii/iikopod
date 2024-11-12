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
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\Modifier\CreateModifierResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\CreateModifierTypeResponseData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeRequestData;
use Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\ModifierType\EditModifierTypeResponseData;
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

    //    public function deleteModifierType(EditModifierTypeRequestData $editModifierTypeRequestData, IntegerId $id): EditModifierTypeResponseData;

    public function createModifier(CreateModifierRequestData $createModifierRequestData): CreateModifierResponseData;

    public function createFoodModifier(CreateFoodModifierRequestData $createFoodModifierRequestData): CreateFoodModifierResponseData;
}

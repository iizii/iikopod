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
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestFailedEvent;
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestSuccessesEvent;
use Infrastructure\Integrations\WelcomeGroup\Exceptions\WelcomeGroupIntegrationException;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\CreateFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\EditFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\CreateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\UpdateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\CreateFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\EditFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\CreateModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\CreateModifierTypeRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\EditModifierTypeRequest;
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
    public function createFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData): CreateFoodCategoryResponseData
    {
        /** @var CreateFoodCategoryResponseData $response */
        $response = $this->send(new CreateFoodCategoryRequest($createFoodCategoryRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateFoodCategory(CreateFoodCategoryRequestData $createFoodCategoryRequestData, IntegerId $id): CreateFoodCategoryResponseData
    {
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
    public function createModifierType(CreateModifierTypeRequestData $createModifierTypeRequestData): CreateModifierTypeResponseData
    {
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
        /** @var CreateModifierResponseData $respone */
        $response = $this->send(new CreateModifierRequest($createModifierRequestData));

        return $response;
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createFoodModifier(CreateFoodModifierRequestData $createFoodModifierRequestData): CreateFoodModifierResponseData
    {
        /** @var CreateFoodModifierResponseData $response */
        $response = $this->send(new CreateFoodModifierRequest($createFoodModifierRequestData));

        return $response;
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
        /** @var EditRestaurantFoodResponseData $response */
        $response = $this->send(new EditRestaurantFoodRequest($id->id, $editRestaurantFoodRequestData));

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

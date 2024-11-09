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
use Illuminate\Log\Context\Repository as LogContext;
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
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestFailedEvent;
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestSuccessesEvent;
use Infrastructure\Integrations\WelcomeGroup\Exceptions\WelcomeGroupIntegrationException;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\CreateFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Food\EditFoodRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\CreateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodCategory\UpdateFoodCategoryRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\FoodModifier\CreateFoodModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\Modifier\CreateModifierRequest;
use Infrastructure\Integrations\WelcomeGroup\Requests\ModifierType\CreateModifierTypeRequest;
use Psr\Log\LoggerInterface;
use Shared\Domain\ValueObjects\IntegerId;
use Shared\Infrastructure\Integrations\AbstractConnector;
use Shared\Infrastructure\Integrations\RequestInterface;

final readonly class WelcomeGroupConnector extends AbstractConnector implements WelcomeGroupConnectorInterface
{
    public function __construct(
        PendingRequest $pendingRequest,
        Dispatcher $eventDispatcher,
        LogContext $logContext,
        LoggerInterface $logger,
        private SignatureCompiler $signatureCompiler,
    ) {
        parent::__construct($pendingRequest, $eventDispatcher, $logContext, $logger);
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
    public function createModifier(CreateModifierRequestData $createModifierRequestData): CreateModifierResponseData
    {
        /** @var CreateModifierResponseData $respone */
        $respone = $this->send(new CreateModifierRequest($createModifierRequestData));

        return $respone;
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

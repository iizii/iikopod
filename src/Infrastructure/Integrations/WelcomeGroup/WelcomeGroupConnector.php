<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup;

use Carbon\CarbonImmutable;
use Domain\Integrations\WelcomeGroup\WelcomeGroupConnectorInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Log\Context\Repository as LogContext;
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestFailedEvent;
use Infrastructure\Integrations\WelcomeGroup\Events\WelcomeGroupRequestSuccessesEvent;
use Infrastructure\Integrations\WelcomeGroup\Exceptions\WelcomeGroupIntegrationException;
use Psr\Log\LoggerInterface;
use Shared\Infrastructure\Integrations\AbstractConnector;
use Shared\Infrastructure\Integrations\RequestInterface;

final readonly class WelcomeGroupConnector extends AbstractConnector implements WelcomeGroupConnectorInterface
{
    public function __construct(
        PendingRequest $pendingRequest,
        Dispatcher $eventDispatcher,
        LogContext $logContext,
        LoggerInterface $logger,
        public SignatureCompiler $signatureCompiler,
    ) {
        parent::__construct($pendingRequest, $eventDispatcher, $logContext, $logger);
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

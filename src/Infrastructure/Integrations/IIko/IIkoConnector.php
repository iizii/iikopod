<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Infrastructure\Integrations\IIko\DataTransferObjects\AuthorizationResponseData;
use Infrastructure\Integrations\IIko\Events\IIkoRequestFailedEvent;
use Infrastructure\Integrations\IIko\Events\IIkoRequestSuccessesEvent;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Infrastructure\Integrations\IIko\Requests\AuthorizationRequest;
use Shared\Infrastructure\Integrations\AbstractConnector;
use Throwable;

final readonly class IIkoConnector extends AbstractConnector implements IikoConnectorInterface
{
    protected function getRequestException(Response $response, Throwable $clientException): Throwable
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

    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }
}

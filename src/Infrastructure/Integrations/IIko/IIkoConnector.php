<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\Events\IIkoRequestFailedEvent;
use Infrastructure\Integrations\IIko\Events\IIkoRequestSuccessesEvent;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Shared\Integrations\AbstractConnector;
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

    protected function getRequestSuccessEvents(): iterable
    {
        return [
            IIkoRequestSuccessesEvent::class,
        ];
    }

    protected function getRequestErrorEvents(): iterable
    {
        return [
            IIkoRequestFailedEvent::class,
        ];
    }
}

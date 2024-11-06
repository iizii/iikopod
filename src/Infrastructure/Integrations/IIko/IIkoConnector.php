<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko;

use Domain\Integrations\Iiko\IikoConnectorInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuRequestData;
use Infrastructure\Integrations\IIko\DataTransferObjects\GetMenuResponse\GetMenuResponseData;
use Infrastructure\Integrations\IIko\Events\IIkoRequestFailedEvent;
use Infrastructure\Integrations\IIko\Events\IIkoRequestSuccessesEvent;
use Infrastructure\Integrations\IIko\Exceptions\IIkoIntegrationException;
use Infrastructure\Integrations\IIko\Requests\GetMenuRequest;
use Shared\Infrastructure\Integrations\AbstractConnector;
use Shared\Infrastructure\Integrations\RequestInterface;

final readonly class IIkoConnector extends AbstractConnector implements IikoConnectorInterface
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function getMenu(
        GetMenuRequestData $getMenuRequestData,
        string $authToken,
    ): GetMenuResponseData {
        /** @var GetMenuResponseData $response */
        $response = $this->send(
            new GetMenuRequest(
                $getMenuRequestData,
                $authToken,
            ),
        );

        return $response;
    }

    protected function getRequestException(Response $response, \Throwable $clientException): \Throwable
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

    protected function headers(RequestInterface $request): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }
}

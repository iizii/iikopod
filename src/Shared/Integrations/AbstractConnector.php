<?php

declare(strict_types=1);

namespace Shared\Integrations;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;

abstract readonly class AbstractConnector
{
    public function __construct(
        private PendingRequest $pendingRequest,
        private Dispatcher $eventDispatcher,
    ) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function execute(RequestInterface $request): Response|ResponseData
    {
        $response = $this
            ->pendingRequest
            ->send(
                $request->getMethod()->value,
                $request->getEndpoint(),
            )
            ->throwIf(fn (Response $response): ?bool => $this->hasRequestFailed($response))
            ->throw(function (Response $response, RequestException $exception) {
                $this->dispatchEvents($this->getRequestErrorEvents(), $response);

                throw $this->getRequestException($response, $exception);
            });

        $this->dispatchEvents($this->getRequestSuccessEvents(), $response);

        return $this->createResponse($response, $request);
    }

    protected function hasRequestFailed(Response $response): ?bool
    {
        return false;
    }

    protected function getRequestException(Response $response, Throwable $clientException): Throwable
    {
        return new HttpIntegrationException(
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
        return [];
    }

    /**
     * @return iterable<class-string>
     */
    protected function getRequestErrorEvents(): iterable
    {
        return [];
    }

    /**
     * @param  iterable<class-string>  $events
     */
    protected function dispatchEvents(iterable $events, Response $response): void
    {
        foreach ($events as $eventClass) {
            $this->eventDispatcher->dispatch(new $eventClass($response));
        }
    }

    private function createResponse(Response $response, RequestInterface $request): Response|ResponseData
    {
        if (! $request instanceof ResponseDataInterface) {
            return $response;
        }

        return $request->createDtoFromResponse($response);
    }
}

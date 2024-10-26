<?php

declare(strict_types=1);

namespace Shared\Integrations;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
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
    public function send(RequestInterface $request): Response|ResponseData
    {
        $response = $this
            ->pendingRequest
            ->send(
                $request->method()->value,
                $request->endpoint(),
                $this->buildParams($request),
            );

        $response = $this->handleResponse($response);

        $this->dispatchEvents($this->getRequestSuccessEvents(), $response);

        return $this->createResponse($response, $request);
    }

    /**
     * @return iterable<Response|ResponseData>
     */
    public function sendAsync(RequestInterface ...$requests): iterable
    {
        $responses = $this
            ->pendingRequest
            ->pool(static function (Pool $pool) use ($requests) {
                foreach ($requests as $request) {
                    $pool->send(
                        $request->method()->value,
                        $request->endpoint(),
                    );
                }
            });

        foreach ($responses as $key => $response) {
            yield $this->createResponse($response, $requests[$key]);
        }
    }

    protected function hasRequestFailed(Response $response): ?bool
    {
        return false;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function headers(): array
    {
        return [];
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

    /**
     * @throws RequestException
     */
    private function handleResponse(Response $response): Response
    {
        return $response
            ->throwIf(fn (Response $response): ?bool => $this->hasRequestFailed($response))
            ->throw(function (Response $response, RequestException $exception) {
                $this->dispatchEvents($this->getRequestErrorEvents(), $response);

                throw $this->getRequestException($response, $exception);
            });
    }

    private function createResponse(Response $response, RequestInterface $request): Response|ResponseData
    {
        if (! $request instanceof ResponseDataInterface) {
            return $response;
        }

        return $request->createDtoFromResponse($response);
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    private function buildParams(RequestInterface $request): array
    {
        $data = $request->data() instanceof Arrayable
            ? $request->data()->toArray()
            : $request->data();

        $params = [];

        if ($request->method() === RequestMethod::POST) {
            $params['json'] = $data;
        }

        if ($request->method() === RequestMethod::GET) {
            $params['query'] = $data;
        }

        if ($this->headers()) {
            $params['headers'] = $this->headers();
        }

        return $params;
    }
}

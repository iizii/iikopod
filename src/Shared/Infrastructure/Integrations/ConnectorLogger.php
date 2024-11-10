<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Integrations;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final class ConnectorLogger
{
    /**
     * @var Collection<array-key, mixed>
     */
    private Collection $logContext;

    private bool $hasError = false;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        $this->logContext = new Collection();
    }

    public function __destruct()
    {
        if ($this->logContext->isEmpty()) {
            return;
        }

        if ($this->hasError) {
            $this->logger->error('FAILED REQUEST', $this->logContext->toArray());

            return;
        }

        $this->logger->info('SUCCESSES REQUEST', $this->logContext->toArray());
    }

    public function logPendingRequest(RequestInterface $request): void
    {
        $this->logContext->put(
            sprintf(
                '[PENDING REQUEST %s]',
                class_basename($request),
            ),
            [
                'request' => get_class($request),
                'method' => $request->method(),
                'endpoint' => $request->endpoint(),
                'headers' => $request->headers(),
                'data' => $request->data(),
            ],
        );
    }

    public function logRawResponse(Response $response): void
    {
        $this->logContext->put(
            '[RAW RESPONSE RECEIVED]',
            [
                'status_code' => $response->getStatusCode(),
            ],
        );
    }

    public function logThrowResponse(RequestInterface $request, Response $response, \Throwable $throwable): void
    {
        $this->hasError = true;

        $this->logContext->put(
            sprintf(
                '[FAILED REQUEST %s]',
                class_basename($request),
            ),
            [
                'request' => get_class($request),
                'method' => $request->method(),
                'endpoint' => $request->endpoint(),
                'headers' => $request->headers(),
                'data' => $request->data(),
                'response_status_code' => $response->getStatusCode(),
                'response_data' => $response->json(),
                'throwable_message' => $throwable->getTraceAsString(),
            ],
        );
    }

    public function logResponse(Response $response): void
    {
        $this->logContext->put(
            '[RESPONSE RECEIVED]',
            [
                'status_code' => $response->getStatusCode(),
            ],
        );
    }
}

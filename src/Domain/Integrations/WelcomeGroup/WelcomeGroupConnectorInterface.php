<?php

declare(strict_types=1);

namespace Domain\Integrations\WelcomeGroup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
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
}

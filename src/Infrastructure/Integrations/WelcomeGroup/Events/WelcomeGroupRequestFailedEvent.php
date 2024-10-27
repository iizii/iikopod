<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\Events;

use Illuminate\Http\Client\Response;
use Shared\Infrastructure\Integrations\RequestEventInterface;
use Shared\Infrastructure\Integrations\RequestInterface;

final readonly class WelcomeGroupRequestFailedEvent implements RequestEventInterface
{
    public function __construct(public RequestInterface $request, public Response $response) {}
}

<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\IIko\Events;

use Illuminate\Http\Client\Response;
use Shared\Integrations\RequestEventInterface;

final readonly class IIkoRequestSuccessesEvent implements RequestEventInterface
{
    public function __construct(public Response $response) {}
}

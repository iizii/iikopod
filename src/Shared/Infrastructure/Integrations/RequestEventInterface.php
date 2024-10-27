<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Integrations;

use Illuminate\Http\Client\Response;

interface RequestEventInterface
{
    public function __construct(RequestInterface $request, Response $response);
}

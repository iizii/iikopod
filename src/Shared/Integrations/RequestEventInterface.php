<?php

declare(strict_types=1);

namespace Shared\Integrations;

use Illuminate\Http\Client\Response;

interface RequestEventInterface
{
    public function __construct(Response $response);
}

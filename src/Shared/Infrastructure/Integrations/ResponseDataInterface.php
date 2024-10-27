<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Integrations;

use Illuminate\Http\Client\Response;

interface ResponseDataInterface
{
    public function createDtoFromResponse(Response $response): ResponseData;
}

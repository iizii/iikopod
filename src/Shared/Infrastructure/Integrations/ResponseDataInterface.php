<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Integrations;

use Illuminate\Http\Client\Response;

interface ResponseDataInterface
{
    /**
     * @return ResponseData|iterable<array-key, ResponseData>
     */
    public function createDtoFromResponse(Response $response): ResponseData|iterable;
}

<?php

declare(strict_types=1);

namespace Shared\Integrations;

use Illuminate\Http\Client\Response;

interface RequestInterface
{
    public function getMethod(): RequestMethod;

    public function getEndpoint(): string;

    public function createDtoFromResponse(Response $response): ResponseData;
}

<?php

declare(strict_types=1);

namespace Domain\Integrations\Iiko;

use Illuminate\Http\Client\Response;
use Shared\Integrations\RequestInterface;
use Shared\Integrations\ResponseData;

interface IikoConnectorInterface
{
    public function execute(RequestInterface $request): Response|ResponseData;
}

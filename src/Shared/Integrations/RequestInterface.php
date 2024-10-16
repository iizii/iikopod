<?php

declare(strict_types=1);

namespace Shared\Integrations;

interface RequestInterface
{
    public function getMethod(): RequestMethod;

    public function getEndpoint(): string;
}

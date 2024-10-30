<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Integrations;

use Illuminate\Contracts\Support\Arrayable;

interface RequestInterface
{
    public function method(): RequestMethod;

    public function endpoint(): string;

    /**
     * @return array<string, string>|Arrayable<string, string>
     */
    public function headers(): array|Arrayable;

    /**
     * @return array<string, string>|Arrayable<string, string>
     */
    public function data(): array|Arrayable;
}

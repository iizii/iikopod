<?php

namespace Infrastructure\Integrations\IIko\DataTransferObjects;

use Spatie\LaravelData\Data;

final class ExternalMenuData extends Data
{
    public function __construct(
        public string $id,
        public string $name
    )
    {}
}

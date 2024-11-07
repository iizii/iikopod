<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects;

use Shared\Infrastructure\Integrations\ResponseData;

final class CreateFoodCategoryRequestData extends ResponseData
{
    public function __construct(
        public readonly string $name,
    ) {}
}

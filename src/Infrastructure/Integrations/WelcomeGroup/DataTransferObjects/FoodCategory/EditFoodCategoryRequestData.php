<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects\FoodCategory;

use Shared\Infrastructure\Integrations\ResponseData;

final class EditFoodCategoryRequestData extends ResponseData
{
    public function __construct(
        public readonly string $name,
    ) {}
}

<?php

declare(strict_types=1);

namespace Domain\Iiko\Events;

use Domain\Iiko\Entities\Menu\ProductCategory;
use Domain\Iiko\Interfaces\WebhookEventInterface;

final readonly class ProductCategoryCreatedEvent implements WebhookEventInterface
{
    public function __construct(public ProductCategory $category) {}
}

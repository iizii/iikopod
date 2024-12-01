<?php

declare(strict_types=1);

namespace Infrastructure\Integrations\WelcomeGroup\DataTransferObjects;

use Application\Iiko\Builders\ItemBuilder;
use Domain\Iiko\Entities\Menu\Item;
use Domain\Settings\OrganizationSetting;
use Domain\WelcomeGroup\Entities\Food;
use Domain\WelcomeGroup\Entities\FoodCategory;
use Domain\WelcomeGroup\Entities\RestaurantFood;
use Domain\WelcomeGroup\Repositories\WelcomeGroupFoodRepositoryInterface;

final class ItemContext
{
    public function __construct(
        public readonly Item $item,
        public readonly ItemBuilder $itemBuilder,
        public readonly Food $food,
        public readonly OrganizationSetting $organizationSetting,
        public readonly FoodCategory $category,
        public readonly WelcomeGroupFoodRepositoryInterface $foodRepo,
        public readonly RestaurantFood $restaurantFood,
    ) {}
}

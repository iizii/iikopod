<?php

declare(strict_types=1);

namespace Domain\Iiko\ValueObjects\Menu;

use Domain\Iiko\Entities\Menu\Nutrition;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, Nutrition>
 */
final class NutritionCollection extends ValueObjectCollection {}

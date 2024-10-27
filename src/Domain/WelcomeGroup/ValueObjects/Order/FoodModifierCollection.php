<?php

declare(strict_types=1);

namespace Domain\WelcomeGroup\ValueObjects\Order;

use Domain\WelcomeGroup\Entities\FoodModifier;
use Shared\Domain\ValueObjects\ValueObjectCollection;

/**
 * @extends ValueObjectCollection<array-key, FoodModifier>
 */
final class FoodModifierCollection extends ValueObjectCollection {}
